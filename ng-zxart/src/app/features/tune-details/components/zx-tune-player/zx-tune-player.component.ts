import {
  AfterViewInit,
  ChangeDetectionStrategy,
  Component,
  ElementRef,
  Input,
  NgZone,
  OnDestroy,
  ViewChild
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import {TuneDetailsDto} from '../../models/tune-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {PlayerService} from '../../../player/services/player.service';
import {PlayerState} from '../../../player/models/player-state';

const ANALYSER_MODES = [4, 0, 2, 6, 8, 10];
const DEFAULT_ANALYSER_MODE = ANALYSER_MODES[0];

@Component({
  selector: 'zx-tune-player',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxButtonComponent,
    TextDirective,
  ],
  templateUrl: './zx-tune-player.component.html',
  styleUrls: ['./zx-tune-player.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTunePlayerComponent implements AfterViewInit, OnDestroy {
  @Input({required: true}) tune!: TuneDetailsDto;
  @ViewChild('analyserHost', {static: true}) private analyserHost!: ElementRef<HTMLElement>;

  readonly state$ = this.playerService.state$;
  private analyser: AudioMotionAnalyzer | null = null;
  private analyserModeIndex = 0;

  constructor(
    private readonly playerService: PlayerService,
    private readonly zone: NgZone,
  ) {}

  ngAfterViewInit(): void {
    const source = this.playerService.getAnalyzerSource();
    if (!source) {
      return;
    }
    this.zone.runOutsideAngular(() => {
      const stage = this.analyserHost.nativeElement;
      this.analyser = new AudioMotionAnalyzer(this.analyserHost.nativeElement, {
        source,
        connectSpeakers: false,
        height: stage.clientHeight,
        mode: DEFAULT_ANALYSER_MODE,
        gradient: 'prism',
        showBgColor: false,
        showPeaks: true,
        smoothing: 0.75,
      });
    });
  }

  ngOnDestroy(): void {
    this.analyser?.destroy();
    this.analyser = null;
  }

  cycleAnalyserMode(): void {
    if (!this.analyser) {
      return;
    }
    this.analyserModeIndex = (this.analyserModeIndex + 1) % ANALYSER_MODES.length;
    this.analyser.mode = ANALYSER_MODES[this.analyserModeIndex];
  }

  handleAnalyserKeydown(event: KeyboardEvent): void {
    if (event.key !== 'Enter' && event.key !== ' ') {
      return;
    }
    event.preventDefault();
    this.cycleAnalyserMode();
  }

  togglePlayback(state: PlayerState): void {
    if (!this.tune.isPlayable || !this.tune.mp3Url) {
      return;
    }
    if (this.isCurrentTune(state)) {
      this.playerService.togglePlay();
      return;
    }
    this.playerService.startPlaylist(`tune-details-${this.tune.id}`, [this.tune], 0);
  }

  isCurrentTune(state: PlayerState): boolean {
    return state.currentIndex >= 0 && state.playlist[state.currentIndex]?.id === this.tune.id;
  }

  isPlaying(state: PlayerState): boolean {
    return this.isCurrentTune(state) && state.isPlaying;
  }

  getProgressPercent(state: PlayerState): string {
    if (!this.isCurrentTune(state) || !state.duration) {
      return '0%';
    }
    const percent = Math.max(0, Math.min(1, state.currentTime / state.duration)) * 100;
    return `${percent.toFixed(2)}%`;
  }
}
