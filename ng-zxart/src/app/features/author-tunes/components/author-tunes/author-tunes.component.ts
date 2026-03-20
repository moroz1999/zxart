import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {map} from 'rxjs';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {PlayerService} from '../../../player/services/player.service';
import {AuthorTunesService} from '../../services/author-tunes.service';

interface YearGroup {
  year: number;
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
  changeDetection: ChangeDetectionStrategy.OnPush,
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
    private authorTunesService: AuthorTunesService,
    private playerService: PlayerService,
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
    this.authorTunesService.getTunes(this.elementId).subscribe({
      next: tunes => {
        this.loading = false;
        this.buildGroups(tunes);
      },
      error: () => {
        this.loading = false;
        this.error = true;
      },
    });
  }

  private buildGroups(tunes: ZxTuneDto[]): void {
    // Sort year descending, title ascending — matches server-side krsort + title sort
    const sorted = [...tunes].sort((a, b) => {
      const yearA = a.year ? parseInt(a.year, 10) : 0;
      const yearB = b.year ? parseInt(b.year, 10) : 0;
      if (yearB !== yearA) return yearB - yearA;
      return a.title.localeCompare(b.title);
    });

    this.playlist = sorted;

    const yearMap = new Map<number, ZxTuneDto[]>();
    for (const tune of sorted) {
      const year = tune.year ? parseInt(tune.year, 10) : 0;
      if (!yearMap.has(year)) yearMap.set(year, []);
      yearMap.get(year)!.push(tune);
    }

    let index = 0;
    this.yearGroups = [...yearMap.entries()].map(([year, group]) => {
      const startIndex = index;
      index += group.length;
      return {year, tunes: group, startIndex};
    });
  }
}
