import {ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Subscription, switchMap} from 'rxjs';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {AuthorTunesService} from '../../../author-tunes/services/author-tunes.service';
import {PlayerService} from '../../../player/services/player.service';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxTuneTableSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';

const PAGE_SIZE = 20;

interface YearGroup {
  year: string | null;
  tunes: ZxTuneDto[];
}

@Component({
  selector: 'zx-author-music-tab',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTuneRowComponent,
    ZxPaginationComponent,
    ZxFilterBarComponent,
    ZxButtonControlsComponent,
    ZxButtonComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxTuneTableSkeletonComponent,
    TextDirective,
    ZxTableComponent,
  ],
  templateUrl: './zx-author-music-tab.component.html',
  styleUrl: './zx-author-music-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorMusicTabComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  @Input() urlBase = '';

  private readonly sortStore = new BehaviorSubject<string>('year-desc');
  private readonly formatGroupStore = new BehaviorSubject<string>('');
  private pageStore = new BehaviorSubject<number>(1);

  error = false;
  loading = true;
  total = 0;
  yearGroups: YearGroup[] = [];
  availableFormatGroups: string[] = [];
  playingTuneId: number | null = null;

  private playlist: ZxTuneDto[] = [];
  private playlistId = '';
  private readonly subscriptions = new Subscription();

  get currentSort(): string { return this.sortStore.getValue(); }
  get currentFormatGroup(): string { return this.formatGroupStore.getValue(); }
  get currentPage(): number { return this.pageStore.getValue(); }
  get pagesAmount(): number { return Math.ceil(this.total / PAGE_SIZE); }

  constructor(
    private readonly authorTunesService: AuthorTunesService,
    private readonly playerService: PlayerService,
    private readonly cdr: ChangeDetectorRef,
    private readonly element: ElementRef<HTMLElement>,
  ) {}

  ngOnInit(): void {
    this.playlistId = `author-music-${this.elementId}`;
    this.pageStore = new BehaviorSubject<number>(this.parsePageFromUrl());
    this.subscriptions.add(
      this.playerService.state$.subscribe(state => {
        const id = state.isPlaying && state.playlistId === this.playlistId
          ? (state.playlist[state.currentIndex]?.id ?? null)
          : null;
        if (id !== this.playingTuneId) {
          this.playingTuneId = id;
          this.cdr.markForCheck();
        }
      }),
    );
    this.subscriptions.add(
      combineLatest([this.sortStore, this.formatGroupStore, this.pageStore]).pipe(
        switchMap(([sort, formatGroup, page]) => {
          this.loading = true;
          this.cdr.markForCheck();
          const {column, dir} = this.parseSortKey(sort);
          const start = (page - 1) * PAGE_SIZE;
          return this.authorTunesService.getTunesPaged(this.elementId, start, PAGE_SIZE, column, dir, formatGroup);
        }),
      ).subscribe({
        next: result => {
          this.loading = false;
          this.total = result.total;
          this.playlist = result.items;
          this.yearGroups = this.buildGroups(result.items, this.sortStore.getValue());
          if (result.availableFormatGroups?.length) {
            this.availableFormatGroups = result.availableFormatGroups;
          }
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.error = true;
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  setSort(sort: string): void {
    this.sortStore.next(sort);
    this.pageStore.next(1);
  }

  setFormatGroup(formatGroup: string): void {
    this.formatGroupStore.next(formatGroup);
    this.pageStore.next(1);
  }

  onPageChange(page: number): void {
    this.pageStore.next(page);
    this.pushPageToUrl(page);
    scrollToElementIfHidden(this.element.nativeElement.closest('zx-tabs'));
  }

  private parsePageFromUrl(): number {
    const match = window.location.pathname.match(/\/page:(\d+)/);
    const page = match ? parseInt(match[1], 10) : 1;
    return page > 0 ? page : 1;
  }

  private pushPageToUrl(page: number): void {
    if (!this.urlBase) return;
    const newUrl = page > 1 ? this.urlBase + 'page:' + page + '/' : this.urlBase;
    window.history.pushState(null, '', newUrl);
  }

  onPlayRequested(tune: ZxTuneDto): void {
    const playable = this.playlist.filter(t => t.isPlayable && t.mp3Url);
    const startIndex = playable.findIndex(t => t.id === tune.id);
    if (startIndex === -1) {
      return;
    }
    this.playerService.startPlaylist(this.playlistId, playable, startIndex);
  }

  onPauseRequested(): void {
    this.playerService.pause();
  }

  private parseSortKey(sort: string): {column: string; dir: string} {
    if (sort === 'year-asc') return {column: 'year', dir: 'asc'};
    if (sort === 'year-desc') return {column: 'year', dir: 'desc'};
    if (sort === 'plays') return {column: 'plays', dir: 'desc'};
    return {column: 'votes', dir: 'desc'};
  }

  private buildGroups(tunes: ZxTuneDto[], sort: string): YearGroup[] {
    if (sort === 'votes' || sort === 'plays') {
      return [{year: null, tunes}];
    }
    const byYear = new Map<string, ZxTuneDto[]>();
    for (const tune of tunes) {
      const year = tune.year ?? 'Unknown';
      const list = byYear.get(year) ?? [];
      list.push(tune);
      byYear.set(year, list);
    }
    const dir = sort === 'year-asc' ? 1 : -1;
    return Array.from(byYear.entries())
      .map(([year, tunes]) => ({year, tunes}))
      .sort((a, b) => dir * (Number(a.year) - Number(b.year)));
  }
}
