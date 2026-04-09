import {ChangeDetectionStrategy, ChangeDetectorRef, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {map} from 'rxjs';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxSortSelectComponent} from '../../../../shared/ui/zx-sort-select/zx-sort-select.component';
import {PlayerService} from '../../../player/services/player.service';
import {MusicBrowserService} from '../../services/music-browser.service';
import {BrowserBaseComponent} from '../../../../shared/browser-base.component';

@Component({
  selector: 'zx-music-browser',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxTuneRowComponent,
    ZxSkeletonComponent,
    ZxCaptionDirective,
    ZxPaginationComponent,
    ZxSortSelectComponent,
  ],
  templateUrl: './zx-music-browser.component.html',
  styleUrls: ['./zx-music-browser.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxMusicBrowserComponent extends BrowserBaseComponent {
  tunes: ZxTuneDto[] = [];
  private playlistId = '';

  readonly playingTuneId$ = this.playerService.state$.pipe(
    map(state => state.isPlaying && state.playlistId === this.playlistId
      ? (state.playlist[state.currentIndex]?.id ?? null)
      : null
    )
  );

  constructor(
    private musicBrowserService: MusicBrowserService,
    private playerService: PlayerService,
    translateService: TranslateService,
    cdr: ChangeDetectorRef,
  ) {
    super(translateService, cdr);
  }

  protected override onBeforeInit(): void {
    this.playlistId = `music-browser-${this.elementId}`;
  }

  playTune(index: number): void {
    const selected = this.tunes[index];
    if (!selected) return;
    const playable = this.tunes.filter(t => t.isPlayable && t.mp3Url);
    const startIndex = playable.findIndex(t => t.id === selected.id);
    if (startIndex === -1) return;
    this.playerService.startPlaylist(this.playlistId, playable, startIndex);
  }

  pauseTune(): void {
    this.playerService.pause();
  }

  protected override fetchPage(start: number, limit: number): void {
    this.musicBrowserService.getPaged(this.elementId, start, limit, this.sorting).subscribe({
      next: response => {
        this.loading = false;
        this.tunes = response.items;
        this.total = response.total;
        this.pagesAmount = Math.ceil(this.total / limit);
        this.cdr.markForCheck();
      },
      error: () => {
        this.loading = false;
        this.error = true;
        this.cdr.markForCheck();
      },
    });
  }
}
