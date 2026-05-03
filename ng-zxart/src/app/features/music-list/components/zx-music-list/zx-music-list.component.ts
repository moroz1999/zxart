import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {catchError, map, startWith} from 'rxjs/operators';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {PlayerService} from '../../../player/services/player.service';
import {MusicListService} from '../../services/music-list.service';

@Component({
  selector: 'zx-music-list-inline',
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
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxMusicListComponent implements OnInit {
  @Input() elementId = 0;
  @Input() compoType = '';

  vm$: Observable<MusicListVm> = of({loading: true, error: false, tunes: []});
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
    this.vm$ = this.buildVm();
  }

  playTune(tunes: ZxTuneDto[], index: number): void {
    const selected = tunes[index];
    if (!selected) {
      return;
    }
    const playable = tunes.filter(t => t.isPlayable && t.mp3Url);
    const startIndex = playable.findIndex(t => t.id === selected.id);
    if (startIndex === -1) {
      return;
    }
    this.playerService.startPlaylist(this.playlistId, playable, startIndex);
  }

  pauseTune(): void {
    this.playerService.pause();
  }

  private buildVm(): Observable<MusicListVm> {
    if (!this.elementId) {
      return of({loading: false, error: true, tunes: []});
    }

    return this.musicListService.getTunes(this.elementId, this.compoType || undefined).pipe(
      map(tunes => ({loading: false, error: false, tunes})),
      catchError(() => of({loading: false, error: true, tunes: []})),
      startWith({loading: true, error: false, tunes: []}),
    );
  }
}

interface MusicListVm {
  readonly loading: boolean;
  readonly error: boolean;
  readonly tunes: ZxTuneDto[];
}
