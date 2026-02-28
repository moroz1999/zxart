import {Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {map} from 'rxjs';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {PlayerService} from '../../../player/services/player.service';
import {MusicListService} from '../../services/music-list.service';

@Component({
  selector: 'zx-music-list',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxTuneRowComponent,
    ZxSkeletonComponent,
    ZxCaptionDirective,
  ],
  templateUrl: './zx-music-list.component.html',
  styleUrls: ['./zx-music-list.component.scss'],
})
export class ZxMusicListComponent implements OnInit {
  @Input() elementId = 0;
  @Input() compoType = '';

  loading = true;
  error = false;
  tunes: ZxTuneDto[] = [];
  private playlistId = '';

  readonly playingTuneId$ = this.playerService.state$.pipe(
    map(state => state.isPlaying && state.playlistId === this.playlistId
      ? (state.playlist[state.currentIndex]?.id ?? null)
      : null
    )
  );

  constructor(
    private musicListService: MusicListService,
    private playerService: PlayerService,
  ) {}

  ngOnInit(): void {
    this.playlistId = `music-list-${this.elementId}${this.compoType ? '-' + this.compoType : ''}`;
    this.loadData();
  }

  playTune(index: number): void {
    const selected = this.tunes[index];
    if (!selected) {
      return;
    }
    const playable = this.tunes.filter(t => t.isPlayable && t.mp3Url);
    const startIndex = playable.findIndex(t => t.id === selected.id);
    if (startIndex === -1) {
      return;
    }
    this.playerService.startPlaylist(this.playlistId, playable, startIndex);
  }

  pauseTune(): void {
    this.playerService.pause();
  }

  private loadData(): void {
    if (!this.elementId) {
      this.loading = false;
      this.error = true;
      return;
    }
    this.loading = true;
    this.error = false;
    this.musicListService.getTunes(this.elementId, this.compoType || undefined).subscribe({
      next: tunes => {
        this.loading = false;
        this.tunes = tunes;
      },
      error: () => {
        this.loading = false;
        this.error = true;
      },
    });
  }
}
