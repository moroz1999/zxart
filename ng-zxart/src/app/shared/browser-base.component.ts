import {ChangeDetectorRef, Directive, inject, Input, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute, Params, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ZxSelectOption} from './ui/zx-select/zx-select.component';

export const BROWSER_SORT_KEYS = [
  'title,asc', 'title,desc',
  'votes,desc', 'votes,asc',
  'year,asc', 'year,desc',
  'date,desc', 'date,asc',
];

const SORT_TRANSLATION_PREFIX = 'prods-list.sorting.';

@Directive()
export abstract class BrowserBaseComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  @Input() fixedSorting: string | null = null;
  @Input() showSorting = true;

  loading = true;
  error = false;
  total = 0;
  currentPage = 1;
  pagesAmount = 0;
  sorting = 'title,asc';
  sortingOptions: ZxSelectOption[] = [];

  protected readonly subscriptions = new Subscription();
  protected readonly sortKeys: string[] = BROWSER_SORT_KEYS;
  protected readonly itemsPerPage: number = 50;

  /**
   * Filter query params owned by the concrete browser. Filled in
   * `onQueryParams` and kept in the URL when the page or the sorting changes.
   */
  protected filterParams: Params = {};

  protected readonly router = inject(Router);
  protected readonly route = inject(ActivatedRoute);

  protected constructor(
    protected readonly translateService: TranslateService,
    protected readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.onBeforeInit();
    this.subscriptions.add(
      this.translateService.stream(this.sortKeys.map(k => SORT_TRANSLATION_PREFIX + k)).subscribe(translations => {
        this.sortingOptions = this.sortKeys.map(k => ({
          value: k,
          label: translations[SORT_TRANSLATION_PREFIX + k] ?? k,
        }));
        this.cdr.markForCheck();
      })
    );

    this.subscriptions.add(this.route.queryParams.subscribe(params => {
      this.currentPage = params['page'] ? +params['page'] : 1;
      this.sorting = this.fixedSorting ?? params['sorting'] ?? 'title,asc';
      this.onQueryParams(params);
      this.loadPage();
    }));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSortingChange(value: string): void {
    this.sorting = value;
    this.currentPage = 1;
    this.navigateWithParams();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.navigateWithParams();
  }

  /** Filter state carried by the pagination links. */
  get paginationQueryParams(): Params {
    return this.filterParams;
  }

  /** Read the browser's own filter params before the page loads. */
  protected onQueryParams(_params: Params): void {}

  /** Write page + sorting to the URL; the queryParams subscription reloads. */
  private navigateWithParams(): void {
    const queryParams: Params = {...this.filterParams};
    if (this.currentPage > 1) {
      queryParams['page'] = this.currentPage;
    }
    if (this.fixedSorting === null && this.sorting && this.sorting !== 'title,asc') {
      queryParams['sorting'] = this.sorting;
    }
    this.router.navigate([], {relativeTo: this.route, queryParams});
  }

  protected onBeforeInit(): void {}

  protected loadPage(): void {
    // elementId 0 = collection mount: the backend resolves the catalogue root by type.
    this.loading = true;
    this.error = false;
    const start = (this.currentPage - 1) * this.itemsPerPage;
    this.fetchPage(start, this.itemsPerPage);
  }

  protected abstract fetchPage(start: number, limit: number): void;
}
