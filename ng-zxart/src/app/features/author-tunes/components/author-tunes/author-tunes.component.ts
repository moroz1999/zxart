import {Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {map} from 'rxjs';
import {ElementsService} from '../../../../shared/services/elements.service';
import {AuthorTunes} from '../../models/author-tunes';
import {AuthorTunesDto, AuthorTunesYearDto} from '../../models/author-tunes-dto';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {PlayerService} from '../../../player/services/player.service';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';

interface YearGroup {
  title: string;
  tunes: ZxTuneDto[];
  startIndex: number;
}

@Component({
  selector: 'zx-author-tunes',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxTuneRowComponent,
    ZxSkeletonComponent,
    ZxCaptionDirective,
    ZxStackComponent,
  ],
  templateUrl: './author-tunes.component.html',
  styleUrls: ['./author-tunes.component.scss'],
})
export class AuthorTunesComponent implements OnInit {
  @Input() elementId = 0;

  loading = true;
  error = false;
  yearGroups: YearGroup[] = [];
  playlist: ZxTuneDto[] = [];
  private playlistId = '';

  readonly playingTuneId$ = this.playerService.state$.pipe(
    map(state => state.isPlaying && state.playlistId === this.playlistId
      ? (state.playlist[state.currentIndex]?.id ?? null)
      : null
    )
  );

  constructor(
    private elementsService: ElementsService,
    private playerService: PlayerService,
    private translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.playlistId = `author-${this.elementId}`;
    this.loadData();
  }

  playTune(index: number): void {
    const selected = this.playlist[index];
    if (!selected) {
      return;
    }
    const playable = this.playlist.filter(item => item.isPlayable && item.mp3Url);
    const startIndex = playable.findIndex(item => item.id === selected.id);
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
    this.elementsService.getModel<AuthorTunesDto, AuthorTunes>(
      this.elementId,
      AuthorTunes,
      {},
      'musicList',
    ).subscribe({
      next: model => {
        this.loading = false;
        this.error = false;
        this.buildGroups(model.tunesByYear ?? []);
      },
      error: () => {
        this.loading = false;
        this.error = true;
      },
    });
  }

  private buildGroups(tunesByYear: AuthorTunesYearDto[]): void {
    let index = 0;
    this.playlist = [];
    this.yearGroups = tunesByYear.map(group => {
      const title = group.year > 0
        ? String(group.year)
        : this.translate.instant('tune.unknownYear');
      const startIndex = index;
      const tunes = group.tunes ?? [];
      for (const tune of tunes) {
        this.playlist.push(tune);
        index += 1;
      }
      return {
        title,
        tunes,
        startIndex,
      };
    });
  }
}
