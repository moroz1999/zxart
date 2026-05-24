import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Subscription, switchMap} from 'rxjs';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {AuthorTunesService} from '../../../author-tunes/services/author-tunes.service';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxTuneTableSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';

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
  ],
  templateUrl: './zx-author-music-tab.component.html',
  styleUrl: './zx-author-music-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorMusicTabComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  @Input() urlBase = '';

  private readonly sortStore = new BehaviorSubject<string>('year-desc');
  private readonly chipStore = new BehaviorSubject<string>('');
  private pageStore = new BehaviorSubject<number>(1);

  error = false;
  loading = true;
  total = 0;
  yearGroups: YearGroup[] = [];
  availableChips: string[] = [];
  playingTuneId: number | null = null;

  private readonly subscriptions = new Subscription();

  get currentSort(): string { return this.sortStore.getValue(); }
  get currentChip(): string { return this.chipStore.getValue(); }
  get currentPage(): number { return this.pageStore.getValue(); }
  get pagesAmount(): number { return Math.ceil(this.total / PAGE_SIZE); }

  constructor(
    private readonly authorTunesService: AuthorTunesService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.pageStore = new BehaviorSubject<number>(this.parsePageFromUrl());
    this.subscriptions.add(
      combineLatest([this.sortStore, this.chipStore, this.pageStore]).pipe(
        switchMap(([sort, chip, page]) => {
          this.loading = true;
          this.cdr.markForCheck();
          const {column, dir} = this.parseSortKey(sort);
          const start = (page - 1) * PAGE_SIZE;
          return this.authorTunesService.getTunesPaged(this.elementId, start, PAGE_SIZE, column, dir, chip);
        }),
      ).subscribe({
        next: result => {
          this.loading = false;
          this.total = result.total;
          this.yearGroups = this.buildGroups(result.items, this.sortStore.getValue());
          if (result.availableFormats?.length) {
            this.availableChips = result.availableFormats;
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

  setChip(chip: string): void {
    this.chipStore.next(chip);
    this.pageStore.next(1);
  }

  onPageChange(page: number): void {
    this.pageStore.next(page);
    this.pushPageToUrl(page);
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
    this.playingTuneId = tune.id;
    this.cdr.markForCheck();
  }

  onPauseRequested(): void {
    this.playingTuneId = null;
    this.cdr.markForCheck();
  }

  private parseSortKey(sort: string): {column: string; dir: string} {
    if (sort === 'year-asc') return {column: 'year', dir: 'asc'};
    if (sort === 'year-desc') return {column: 'year', dir: 'desc'};
    return {column: 'votes', dir: 'desc'};
  }

  private buildGroups(tunes: ZxTuneDto[], sort: string): YearGroup[] {
    if (sort === 'votes') {
      return [{year: null, tunes}];
    }
    const byYear = new Map<string, ZxTuneDto[]>();
    for (const tune of tunes) {
      const year = tune.year ?? 'Unknown';
      const list = byYear.get(year) ?? [];
      list.push(tune);
      byYear.set(year, list);
    }
    return Array.from(byYear.entries())
      .map(([year, tunes]) => ({year, tunes}))
      .sort((a, b) => Number(b.year) - Number(a.year));
  }
}
