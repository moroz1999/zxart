import {Component, Input} from '@angular/core';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {TranslateModule} from '@ngx-translate/core';
import {PlayerService} from '../../services/player.service';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';

type LegacyTune = {
  id: number | string;
  author?: string;
  title?: string;
  url?: string;
  link?: string;
  mp3FilePath?: string | null;
  votes?: number;
  userVote?: number | null;
};

type LegacyWindow = Window & {
  zxMusicLists?: Record<string, LegacyTune[]>;
  musicList?: LegacyTune[];
};

@Component({
  selector: 'zx-legacy-play',
  standalone: true,
  imports: [MatButtonModule, MatIconModule, TranslateModule],
  templateUrl: './legacy-play-button.component.html',
  styleUrls: ['./legacy-play-button.component.scss'],
})
export class LegacyPlayButtonComponent {
  private playlistIdValue = '';
  private indexValue = 0;
  private tuneIdValue: number | null = null;

  @Input('playlist-id')
  set playlistId(value: string | null) {
    this.playlistIdValue = value ?? '';
  }

  @Input()
  set index(value: number | string | null) {
    const parsed = Number(value ?? 0);
    this.indexValue = Number.isFinite(parsed) ? parsed : 0;
  }

  @Input('tune-id')
  set tuneId(value: number | string | null) {
    if (value === null || value === undefined) {
      this.tuneIdValue = null;
      return;
    }
    const parsed = Number(value);
    this.tuneIdValue = Number.isFinite(parsed) ? parsed : null;
  }

  constructor(private playerService: PlayerService) {}

  play(): void {
    const playlist = this.loadPlaylist();
    if (!playlist.length) {
      return;
    }
    const index = this.resolveIndex(playlist);
    this.playerService.startPlaylist(this.playlistIdValue || 'legacy', playlist, index);
  }

  private resolveIndex(playlist: ZxTuneDto[]): number {
    if (this.indexValue >= 0 && this.indexValue < playlist.length) {
      return this.indexValue;
    }
    if (this.tuneIdValue !== null) {
      const foundIndex = playlist.findIndex(tune => tune.id === this.tuneIdValue);
      if (foundIndex >= 0) {
        return foundIndex;
      }
    }
    return 0;
  }

  private loadPlaylist(): ZxTuneDto[] {
    const legacyWindow = window as LegacyWindow;
    const list =
      (this.playlistIdValue && legacyWindow.zxMusicLists?.[this.playlistIdValue]) ||
      legacyWindow.musicList ||
      [];
    return list.map(item => this.mapLegacyTune(item)).filter(item => item.mp3Url);
  }

  private mapLegacyTune(item: LegacyTune): ZxTuneDto {
    const mp3Url = item.mp3FilePath ?? null;
    const authors = (item.author ?? '')
      .split(',')
      .map(author => author.trim())
      .filter(Boolean)
      .map(name => ({name, url: ''}));

    return {
      id: Number(item.id),
      title: item.title ?? '',
      url: item.url ?? item.link ?? '',
      authors,
      format: '',
      year: null,
      votes: item.votes ?? 0,
      votesAmount: 0,
      userVote: item.userVote ?? null,
      denyVoting: false,
      commentsAmount: 0,
      plays: 0,
      party: null,
      release: null,
      isPlayable: Boolean(mp3Url),
      isRealtime: false,
      compo: null,
      mp3Url,
      originalFileUrl: null,
      trackerFileUrl: null,
    };
  }
}
