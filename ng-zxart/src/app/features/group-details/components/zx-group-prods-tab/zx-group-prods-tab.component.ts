import {ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, inject, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute, ParamMap, Router} from '@angular/router';
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
import {ZxLoadingStateDirective} from '../../../../shared/ui/zx-loading-state/zx-loading-state.directive';

const DEFAULT_PAGE_SIZE = 12;
const OWN_PAGE_SIZE = 15;

/**
 * One work ready to render. The card model is built when the page loads:
 * rebuilding it per change detection hands the card a new model on every check,
 * and a screenshot gallery told its urls changed goes back to the first shot —
 * on the very check the hover triggers.
 */
interface WorkEntry {
  key: number;
  release: GroupReleaseEntry | null;
  prod: ZxProd | null;
}

interface YearGroup {
  year: number | null;
  prods: WorkEntry[];
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
    ZxLoadingStateDirective,
  ],
  templateUrl: './zx-group-prods-tab.component.html',
  styleUrl: './zx-group-prods-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupProdsTabComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  /** Roles the group can be filtered by; a single entry hides the filter. */
  @Input() scopes: readonly GroupProdsScope[] = ['own'];

  private readonly scopeStore = new BehaviorSubject<GroupProdsScope>('own');
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

  get activeScope(): GroupProdsScope { return this.scopeStore.getValue(); }
  get activeType(): string { return this.typeStore.getValue(); }
  get activeCategory(): number { return this.categoryStore.getValue(); }
  get currentSort(): string { return this.sortStore.getValue(); }
  get currentPage(): number { return this.pageStore.getValue(); }
  get pageSize(): number { return this.activeScope === 'own' ? OWN_PAGE_SIZE : DEFAULT_PAGE_SIZE; }
  get pagesAmount(): number { return Math.ceil(this.total / this.pageSize); }
  get isReleases(): boolean { return this.activeScope === 'releases'; }
  get showCategoryFilter(): boolean { return !this.isReleases && this.availableCategories.length > 0; }

  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);

  constructor(
    private readonly prodsApiService: GroupProdsApiService,
    private readonly cdr: ChangeDetectorRef,
    private readonly element: ElementRef<HTMLElement>,
  ) {}

  ngOnInit(): void {
    this.scopeStore.next(this.scopes[0] ?? 'own');
    this.pageStore = new BehaviorSubject<number>(this.pageFromParams(this.route.snapshot.queryParamMap));
    this.subscriptions.add(this.route.queryParamMap.subscribe(params => {
      const page = this.pageFromParams(params);
      if (page !== this.pageStore.getValue()) {
        this.pageStore.next(page);
      }
    }));
    this.subscriptions.add(
      combineLatest([this.scopeStore, this.typeStore, this.categoryStore, this.sortStore, this.pageStore]).pipe(
        switchMap(([scope, type, categoryId, sort, page]) => {
          this.loading = true;
          this.cdr.markForCheck();
          const {sortKey, sortDir} = this.parseSortKey(sort);
          const start = (page - 1) * this.pageSize;
          return this.prodsApiService.getProds(this.elementId, scope, start, this.pageSize, sortKey, sortDir, type, categoryId);
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

  /** Release types and categories are scope-specific, so switching role clears them. */
  setScope(scope: GroupProdsScope): void {
    if (scope === this.activeScope) {
      return;
    }
    this.availableTypes = [];
    this.availableCategories = [];
    this.typeStore.next('');
    this.categoryStore.next(0);
    this.scopeStore.next(scope);
    this.setPage(1);
  }

  getScopeLabelKey(scope: GroupProdsScope): string {
    return `group-details.works.${scope}`;
  }

  trackByScope(_index: number, scope: GroupProdsScope): string {
    return scope;
  }

  setType(type: string): void {
    this.typeStore.next(type);
    this.setPage(1);
  }

  setCategory(categoryId: number): void {
    this.categoryStore.next(categoryId);
    this.setPage(1);
  }

  setSort(sort: string): void {
    this.sortStore.next(sort);
    this.setPage(1);
  }

  onPageChange(page: number): void {
    this.setPage(page);
    scrollToElementIfHidden(this.element.nativeElement.closest('zx-tabs'));
  }

  getReleaseTypeLabelKey(type: string): string {
    return `group-details.release-type.${type}`;
  }

  trackByEntry(_index: number, entry: WorkEntry): number {
    return entry.key;
  }

  private toEntry(item: GroupProdItem): WorkEntry {
    const isProd = item.type === 'prod';

    return {
      key: item.id,
      release: isProd ? null : item as GroupReleaseEntry,
      prod: isProd ? new ZxProd(item as GroupProdEntry) : null,
    };
  }

  private pageFromParams(params: ParamMap): number {
    return Math.max(1, Number(params.get('page')) || 1);
  }

  /** The page lives in the `page` query param; the subscription reloads the list. */
  private setPage(page: number): void {
    void this.router.navigate([], {
      relativeTo: this.route,
      queryParams: {page: page > 1 ? page : null},
      queryParamsHandling: 'merge',
    });
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
      return [{year: null, prods: items.map(item => this.toEntry(item))}];
    }
    const byYear = new Map<number, WorkEntry[]>();
    for (const item of items) {
      const year = this.getItemYear(item);
      const list = byYear.get(year) ?? [];
      list.push(this.toEntry(item));
      byYear.set(year, list);
    }
    const dir = sort === 'year-asc' ? 1 : -1;
    return Array.from(byYear.entries())
      .map(([year, prods]) => ({year, prods}))
      .sort((a, b) => dir * ((a.year ?? 0) - (b.year ?? 0)));
  }
}
