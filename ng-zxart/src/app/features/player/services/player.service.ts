import {Injectable} from '@angular/core';
import {BehaviorSubject, Observable} from 'rxjs';
import {PlayerState} from '../models/player-state';
import {RepeatMode} from '../models/repeat-mode';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {RadioApiService} from './radio-api.service';
import {EMPTY_RADIO_CRITERIA, RadioCriteria} from '../models/radio-criteria';
import {RadioPreset} from '../models/radio-preset';
import {TunePlayService} from './tune-play.service';
import {RadioCriteriaStorageService} from './radio-criteria-storage.service';
import {AnalyticsService} from '../../../shared/services/analytics.service';

type BroadcastMessage = {
  type: 'exclusive';
  senderId: string;
};

type PlaybackMode = 'once' | 'repeat-one' | 'repeat-all' | 'shuffle-all';

const INITIAL_STATE: PlayerState = {
  visible: false,
  mode: null,
  playlistId: null,
  playlist: [],
  currentIndex: -1,
  isPlaying: false,
  shuffleEnabled: false,
  repeatMode: 'off',
  currentTime: 0,
  duration: 0,
};

@Injectable({
  providedIn: 'root'
})
export class PlayerService {
  private readonly audio = new Audio();
  private readonly stateSubject = new BehaviorSubject<PlayerState>(INITIAL_STATE);
  private readonly tabId = this.createTabId();
  private shuffleOrder: number[] = [];
  private shufflePosition = 0;
  private playTimerId: number | null = null;
  private playStartMs: number | null = null;
  private accumulatedSeconds = 0;
  private loggedPlay = false;
  private loggedTuneId: number | null = null;
  private originalTitle: string | null = null;
  private currentCriteria: RadioCriteria = EMPTY_RADIO_CRITERIA;
  private currentPreset: RadioPreset | null = null;
  private readonly criteriaSubject = new BehaviorSubject<RadioCriteria>(EMPTY_RADIO_CRITERIA);
  private readonly presetSubject = new BehaviorSubject<RadioPreset | null>(null);
  private broadcastChannel: BroadcastChannel | null = null;

  state$: Observable<PlayerState> = this.stateSubject.asObservable();
  criteria$: Observable<RadioCriteria> = this.criteriaSubject.asObservable();
  preset$: Observable<RadioPreset | null> = this.presetSubject.asObservable();

  constructor(
    private radioApiService: RadioApiService,
    private tunePlayService: TunePlayService,
    private criteriaStorageService: RadioCriteriaStorageService,
    private analyticsService: AnalyticsService,
  ) {
    this.attachAudioEvents();
    this.initBroadcast();
    this.criteriaStorageService.loadCriteria().subscribe(criteria => {
      this.currentCriteria = criteria;
      this.criteriaSubject.next(criteria);
    });
  }

  get state(): PlayerState {
    return this.stateSubject.value;
  }

  get currentTune(): ZxTuneDto | null {
    if (this.state.currentIndex < 0) {
      return null;
    }
    return this.state.playlist[this.state.currentIndex] ?? null;
  }

  startPlaylist(playlistId: string, playlist: ZxTuneDto[], index: number): void {
    if (!playlist.length) {
      return;
    }
    this.stopPlayback();
    this.currentPreset = null;
    this.presetSubject.next(null);
    const safeIndex = Math.max(0, Math.min(index, playlist.length - 1));
    this.updateState({
      visible: true,
      mode: 'playlist',
      playlistId,
      playlist,
      currentIndex: safeIndex,
      currentTime: 0,
      duration: 0,
    });
    this.resetShuffle();
    this.playCurrent();
  }

  startRadio(criteria: RadioCriteria, preset: RadioPreset | null): void {
    this.updateCriteria(criteria, preset);
    this.updateState({
      visible: true,
      mode: 'radio',
      playlistId: null,
      playlist: [],
      currentIndex: -1,
      currentTime: 0,
      duration: 0,
    });
    this.fetchAndPlayRadioTune();
  }

  togglePlay(): void {
    if (this.state.isPlaying) {
      this.pause();
    } else {
      this.resume();
    }
  }

  resume(): void {
    if (!this.currentTune) {
      return;
    }
    this.audio.play().catch(() => undefined);
  }

  pause(): void {
    this.audio.pause();
  }

  stop(): void {
    this.audio.pause();
    this.audio.currentTime = 0;
    this.stopPlayTimer();
    this.updateState({currentTime: 0, isPlaying: false});
    this.resetDocumentTitle();
  }

  next(): void {
    if (this.state.mode === 'radio') {
      this.fetchAndPlayRadioTune();
      return;
    }
    this.playNextInPlaylist();
  }

  previous(): void {
    if (this.state.mode === 'radio') {
      return;
    }
    const previousIndex = this.state.currentIndex - 1;
    if (previousIndex >= 0) {
      this.setCurrentIndex(previousIndex);
      this.playCurrent();
    }
  }

  seekToPercent(percent: number): void {
    if (!this.audio.duration || Number.isNaN(this.audio.duration)) {
      return;
    }
    const nextTime = this.audio.duration * percent;
    this.audio.currentTime = nextTime;
    this.updateState({currentTime: nextTime});
  }

  setRepeatMode(mode: RepeatMode): void {
    this.updateState({repeatMode: mode});
  }

  setShuffleEnabled(enabled: boolean): void {
    this.updateState({shuffleEnabled: enabled});
    this.resetShuffle();
  }

  setPlaybackMode(mode: PlaybackMode): void {
    switch (mode) {
      case 'repeat-one':
        this.setShuffleEnabled(false);
        this.setRepeatMode('one');
        break;
      case 'repeat-all':
        this.setShuffleEnabled(false);
        this.setRepeatMode('all');
        break;
      case 'shuffle-all':
        this.setRepeatMode('all');
        this.setShuffleEnabled(true);
        break;
      default:
        this.setShuffleEnabled(false);
        this.setRepeatMode('off');
        break;
    }
  }

  closePlayer(): void {
    this.stopPlayback();
    this.updateState({...INITIAL_STATE});
    this.resetDocumentTitle();
  }

  getCriteria(): RadioCriteria {
    return this.currentCriteria;
  }

  getPreset(): RadioPreset | null {
    return this.currentPreset;
  }

  updateCurrentTune(update: Partial<ZxTuneDto>): void {
    const tune = this.currentTune;
    if (!tune) {
      return;
    }
    const playlist = [...this.state.playlist];
    playlist[this.state.currentIndex] = {...tune, ...update};
    this.updateState({playlist});
  }

  private fetchAndPlayRadioTune(): void {
    this.radioApiService.getNextTune(this.currentCriteria).subscribe({
      next: tune => {
        this.stopPlayback();
        this.updateState({
          visible: true,
          mode: 'radio',
          playlist: [tune],
          currentIndex: 0,
          currentTime: 0,
          duration: 0,
        });
        this.playCurrent();
      },
      error: () => {
        this.stopPlayback();
        this.updateState({isPlaying: false});
        this.resetDocumentTitle();
      },
    });
  }

  private playCurrent(): void {
    const tune = this.currentTune;
    if (!tune || !tune.mp3Url) {
      return;
    }
    this.setDocumentTitleForTune(tune);
    if (this.loggedTuneId !== tune.id) {
      this.resetPlayTimer();
      this.loggedTuneId = tune.id;
    }
    this.audio.src = tune.mp3Url;
    this.audio.currentTime = 0;
    this.audio.play().catch(() => undefined);
  }

  private playNextInPlaylist(): void {
    if (!this.currentTune) {
      return;
    }
    if (this.state.repeatMode === 'one') {
      this.playCurrent();
      return;
    }
    const nextIndex = this.getNextIndex();
    if (nextIndex === null) {
      this.stopPlayback();
      this.updateState({isPlaying: false});
      this.resetDocumentTitle();
      return;
    }
    this.setCurrentIndex(nextIndex);
    this.playCurrent();
  }

  private getNextIndex(): number | null {
    if (this.state.shuffleEnabled) {
      const nextPosition = this.shufflePosition + 1;
      if (nextPosition < this.shuffleOrder.length) {
        this.shufflePosition = nextPosition;
        return this.shuffleOrder[this.shufflePosition];
      }
      if (this.state.repeatMode === 'all') {
        this.resetShuffle();
        return this.shuffleOrder[0] ?? null;
      }
      return null;
    }

    const nextIndex = this.state.currentIndex + 1;
    if (nextIndex < this.state.playlist.length) {
      return nextIndex;
    }
    if (this.state.repeatMode === 'all') {
      return 0;
    }
    return null;
  }

  private setCurrentIndex(index: number): void {
    this.updateState({currentIndex: index, currentTime: 0, duration: 0});
    if (this.state.shuffleEnabled) {
      const orderIndex = this.shuffleOrder.indexOf(index);
      if (orderIndex >= 0) {
        this.shufflePosition = orderIndex;
      }
    }
  }

  private resetShuffle(): void {
    if (!this.state.shuffleEnabled || this.state.playlist.length === 0) {
      this.shuffleOrder = [];
      this.shufflePosition = 0;
      return;
    }

    const total = this.state.playlist.length;
    const indices = Array.from({length: total}, (_, i) => i);
    const currentIndex = this.state.currentIndex >= 0 ? this.state.currentIndex : 0;
    indices.splice(currentIndex, 1);

    for (let i = indices.length - 1; i > 0; i -= 1) {
      const j = Math.floor(Math.random() * (i + 1));
      [indices[i], indices[j]] = [indices[j], indices[i]];
    }

    this.shuffleOrder = [currentIndex, ...indices];
    this.shufflePosition = 0;
  }

  private stopPlayback(): void {
    this.audio.pause();
    this.audio.currentTime = 0;
    this.stopPlayTimer();
  }

  private attachAudioEvents(): void {
    this.audio.addEventListener('timeupdate', () => {
      this.updateState({
        currentTime: this.audio.currentTime,
        duration: Number.isFinite(this.audio.duration) ? this.audio.duration : 0,
      });
      this.updateMediaPositionState();
    });

    this.audio.addEventListener('ended', () => {
      if (this.state.mode === 'radio') {
        if (this.state.repeatMode === 'one') {
          this.playCurrent();
        } else {
          this.fetchAndPlayRadioTune();
        }
        return;
      }
      this.playNextInPlaylist();
    });

    this.audio.addEventListener('play', () => {
      this.updateState({isPlaying: true});
      this.startPlayTimer();
      this.broadcastExclusive();
      if (this.currentTune) {
        this.setDocumentTitleForTune(this.currentTune);
      }
      this.updateMediaSession();
    });

    this.audio.addEventListener('pause', () => {
      this.updateState({isPlaying: false});
      this.stopPlayTimer();
    });
  }

  private startPlayTimer(): void {
    if (this.playTimerId !== null) {
      return;
    }
    this.playStartMs = Date.now();
    this.playTimerId = window.setInterval(() => this.checkPlayThreshold(), 1000);
  }

  private stopPlayTimer(): void {
    if (this.playStartMs !== null) {
      this.accumulatedSeconds += (Date.now() - this.playStartMs) / 1000;
      this.playStartMs = null;
    }
    if (this.playTimerId !== null) {
      clearInterval(this.playTimerId);
      this.playTimerId = null;
    }
  }

  private resetPlayTimer(): void {
    this.stopPlayTimer();
    this.accumulatedSeconds = 0;
    this.loggedPlay = false;
  }

  private checkPlayThreshold(): void {
    const tune = this.currentTune;
    if (!tune || !this.audio.duration || Number.isNaN(this.audio.duration)) {
      return;
    }
    if (this.playStartMs === null) {
      return;
    }
    const elapsed = this.accumulatedSeconds + (Date.now() - this.playStartMs) / 1000;
    const percent = elapsed / this.audio.duration;

    if (percent >= 0.75) {
      this.logPlayOnce(tune.id);
    }

    if (elapsed > this.audio.duration) {
      this.accumulatedSeconds = 0;
      this.loggedPlay = false;
      this.playStartMs = Date.now();
    }
  }

  private logPlayOnce(tuneId: number): void {
    if (this.loggedPlay) {
      return;
    }
    this.loggedPlay = true;
    this.tunePlayService.logPlay(tuneId, this.state.mode === 'radio' ? 'radio' : 'page').subscribe();

    this.analyticsService.reachGoal('musicplay');
  }

  private updateMediaSession(): void {
    const tune = this.currentTune;
    if (!tune || !(navigator as Navigator & {mediaSession?: MediaSession}).mediaSession) {
      return;
    }
    const mediaSession = (navigator as Navigator & {mediaSession: MediaSession}).mediaSession;
    mediaSession.metadata = new MediaMetadata({
      title: tune.title,
      artist: tune.authors.map(author => author.name).join(', '),
    });

    mediaSession.setActionHandler('play', () => this.resume());
    mediaSession.setActionHandler('pause', () => this.pause());
    mediaSession.setActionHandler('nexttrack', () => this.next());
    mediaSession.setActionHandler('previoustrack', this.state.mode === 'radio' ? null : () => this.previous());
    mediaSession.setActionHandler('seekto', details => {
      if (details.seekTime !== undefined && this.audio.duration) {
        this.audio.currentTime = details.seekTime;
      }
    });
  }

  private updateMediaPositionState(): void {
    const mediaSession = (navigator as Navigator & {mediaSession?: MediaSession}).mediaSession;
    if (!mediaSession || !('setPositionState' in mediaSession)) {
      return;
    }
    if (!Number.isFinite(this.audio.duration) || !Number.isFinite(this.audio.currentTime)) {
      return;
    }
    mediaSession.setPositionState({
      duration: this.audio.duration,
      playbackRate: this.audio.playbackRate,
      position: this.audio.currentTime,
    });
  }

  private initBroadcast(): void {
    const hasBroadcastChannel = typeof BroadcastChannel !== 'undefined';
    if (hasBroadcastChannel) {
      this.broadcastChannel = new BroadcastChannel('zx-player');
      this.broadcastChannel.addEventListener('message', event => {
        const message = event.data as BroadcastMessage;
        this.handleBroadcast(message);
      });
    } else {
      const win = window as Window;
      win.addEventListener('storage', (event: StorageEvent) => {
        if (event.key !== 'zx_player_lock' || !event.newValue) {
          return;
        }
        try {
          const message = JSON.parse(event.newValue) as BroadcastMessage;
          this.handleBroadcast(message);
        } catch {
          return;
        }
      });
    }
  }

  private broadcastExclusive(): void {
    const message: BroadcastMessage = {type: 'exclusive', senderId: this.tabId};
    if (this.broadcastChannel) {
      this.broadcastChannel.postMessage(message);
    } else {
      localStorage.setItem('zx_player_lock', JSON.stringify(message));
    }
  }

  private handleBroadcast(message: BroadcastMessage): void {
    if (message.type !== 'exclusive' || message.senderId === this.tabId) {
      return;
    }
    this.closePlayer();
  }

  private updateState(partial: Partial<PlayerState>): void {
    this.stateSubject.next({...this.stateSubject.value, ...partial});
  }

  private setDocumentTitleForTune(tune: ZxTuneDto): void {
    if (typeof document === 'undefined') {
      return;
    }
    if (this.originalTitle === null) {
      this.originalTitle = document.title;
    }
    const artist = tune.authors.map(author => author.name).join(', ');
    document.title = artist ? `${artist} - ${tune.title}` : tune.title;
  }

  private resetDocumentTitle(): void {
    if (typeof document === 'undefined') {
      return;
    }
    if (this.originalTitle === null) {
      return;
    }
    document.title = this.originalTitle;
  }

  private updateCriteria(criteria: RadioCriteria, preset: RadioPreset | null): void {
    this.currentCriteria = criteria;
    this.currentPreset = preset;
    this.criteriaStorageService.saveCriteria(criteria).subscribe();
    this.criteriaSubject.next(criteria);
    this.presetSubject.next(preset);
  }

  private createTabId(): string {
    if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
      return crypto.randomUUID();
    }
    return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
  }
}
