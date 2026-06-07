import {ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Subscription, switchMap} from 'rxjs';
import {
  GroupProdCategory,
  GroupProdEntry,
  GroupProdItem,
  GroupProdsApiService,
  GroupProdsScope,
  GroupReleaseEntry,
} from '../../services/group-prods-api.service';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxProdBlockComponent} from '../../../../entities/zx-prod-block/zx-prod-block.component';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxProdsGridDirective} from '../../../../shared/directives/prods-grid.directive';
import {ZxProdReleaseCardComponent} from '../../../../entities/zx-prod-release-card/zx-prod-release-card.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';

const DEFAULT_PAGE_SIZE = 12;
const OWN_PAGE_SIZE = 15;

interface YearGroup {
  year: number | null;
  prods: GroupProdItem[];
}

@Component({
  selector: 'zx-group-prods-tab',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPaginationComponent,
    ZxProdBlockComponent,
    ZxFilterBarComponent,
    ZxButtonControlsComponent,
    ZxButtonComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxProdsListSkeletonComponent,
    TextDirective,
    ZxProdsGridDirective,
    ZxProdReleaseCardComponent,
  ],
  templateUrl: './zx-group-prods-tab.component.html',
  styleUrl: './zx-group-prods-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupProdsTabComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  @Input() scope: GroupProdsScope = 'own';
  @Input() urlBase = '';

  private readonly typeStore = new BehaviorSubject<string>('');
  private readonly categoryStore = new BehaviorSubject<number>(0);
  private readonly sortStore = new BehaviorSubject<string>('year-desc');
  private pageStore = new BehaviorSubject<number>(1);

  error = false;
  loading = true;
  total = 0;
  yearGroups: YearGroup[] = [];
  availableTypes: string[] = [];
  availableCategories: GroupProdCategory[] = [];

  private readonly subscriptions = new Subscription();

  get activeType(): string { return this.typeStore.getValue(); }
  get activeCategory(): number { return this.categoryStore.getValue(); }
  get currentSort(): string { return this.sortStore.getValue(); }
  get currentPage(): number { return this.pageStore.getValue(); }
  get pageSize(): number { return this.scope === 'own' ? OWN_PAGE_SIZE : DEFAULT_PAGE_SIZE; }
  get pagesAmount(): number { return Math.ceil(this.total / this.pageSize); }
  get isReleases(): boolean { return this.scope === 'releases'; }
  get showCategoryFilter(): boolean { return !this.isReleases && this.availableCategories.length > 0; }

  constructor(
    private readonly prodsApiService: GroupProdsApiService,
    private readonly cdr: ChangeDetectorRef,
    private readonly element: ElementRef<HTMLElement>,
  ) {}

  ngOnInit(): void {
    this.pageStore = new BehaviorSubject<number>(this.parsePageFromUrl());
    this.subscriptions.add(
      combineLatest([this.typeStore, this.categoryStore, this.sortStore, this.pageStore]).pipe(
        switchMap(([type, categoryId, sort, page]) => {
          this.loading = true;
          this.cdr.markForCheck();
          const {sortKey, sortDir} = this.parseSortKey(sort);
          const start = (page - 1) * this.pageSize;
          return this.prodsApiService.getProds(this.elementId, this.scope, start, this.pageSize, sortKey, sortDir, type, categoryId);
        }),
      ).subscribe({
        next: result => {
          this.loading = false;
          this.total = result.total;
          this.availableTypes = result.availableTypes;
          this.availableCategories = result.availableCategories;
          this.yearGroups = this.buildGroups(result.items, this.sortStore.getValue());
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

  setType(type: string): void {
    this.typeStore.next(type);
    this.pageStore.next(1);
  }

  setCategory(categoryId: number): void {
    this.categoryStore.next(categoryId);
    this.pageStore.next(1);
  }

  setSort(sort: string): void {
    this.sortStore.next(sort);
    this.pageStore.next(1);
  }

  onPageChange(page: number): void {
    this.pageStore.next(page);
    this.pushPageToUrl(page);
    scrollToElementIfHidden(this.element.nativeElement.closest('zx-tabs'));
  }

  getReleaseTypeLabelKey(type: string): string {
    return `group-details.release-type.${type}`;
  }

  isProdEntry(item: GroupProdItem): item is GroupProdEntry {
    return item.type === 'prod';
  }

  toProdModel(item: GroupProdEntry): ZxProd {
    return new ZxProd(item);
  }

  asRelease(item: GroupProdItem): GroupReleaseEntry {
    return item as GroupReleaseEntry;
  }

  asProd(item: GroupProdItem): GroupProdEntry {
    return item as GroupProdEntry;
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

  private parseSortKey(sort: string): {sortKey: string; sortDir: string} {
    if (sort === 'year-asc') return {sortKey: 'year', sortDir: 'asc'};
    if (sort === 'votes') return {sortKey: 'votes', sortDir: 'desc'};
    return {sortKey: 'year', sortDir: 'desc'};
  }

  private getItemYear(item: GroupProdItem): number {
    if (item.type === 'release') {
      return item.year ?? 0;
    }
    return item.year ? Number(item.year) : 0;
  }

  private buildGroups(items: GroupProdItem[], sort: string): YearGroup[] {
    if (sort === 'votes') {
      return [{year: null, prods: items}];
    }
    const byYear = new Map<number, GroupProdItem[]>();
    for (const item of items) {
      const year = this.getItemYear(item);
      const list = byYear.get(year) ?? [];
      list.push(item);
      byYear.set(year, list);
    }
    const dir = sort === 'year-asc' ? 1 : -1;
    return Array.from(byYear.entries())
      .map(([year, prods]) => ({year, prods}))
      .sort((a, b) => dir * ((a.year ?? 0) - (b.year ?? 0)));
  }
}
