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
  /**
   * Legacy web-component mounts manage pagination through `window.location` +
   * `history.pushState` (`page:N/`). SPA routes keep pagination in router query
   * params and may either expose sorting there or provide a fixed page sort.
   */
  @Input() manageUrl = true;
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
  protected urlBase = '';

  protected readonly router = inject(Router, {optional: true});
  protected readonly route = inject(ActivatedRoute, {optional: true});

  protected constructor(
    protected readonly translateService: TranslateService,
    protected readonly cdr: ChangeDetectorRef,
  ) {}

  /** SPA route mode: page + sorting come from / go to the router query params. */
  protected get useRouter(): boolean {
    return !this.manageUrl && this.router != null && this.route != null;
  }

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

    if (this.useRouter) {
      this.subscriptions.add(this.route!.queryParams.subscribe(params => {
        this.currentPage = params['page'] ? +params['page'] : 1;
        this.sorting = this.fixedSorting ?? params['sorting'] ?? 'title,asc';
        this.loadPage();
      }));
    } else {
      this.urlBase = this.parseUrlBase();
      this.currentPage = this.parsePageFromUrl();
      this.loadPage();
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSortingChange(value: string): void {
    this.sorting = value;
    this.currentPage = 1;
    if (this.useRouter) {
      this.navigateWithParams();
    } else {
      this.updateUrl(1);
      this.loadPage();
    }
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    if (this.useRouter) {
      this.navigateWithParams();
    } else {
      this.updateUrl(page);
      this.loadPage();
    }
  }

  /** SPA route mode: write page + sorting to the URL; the queryParams subscription reloads. */
  private navigateWithParams(): void {
    const queryParams: Params = {};
    if (this.currentPage > 1) {
      queryParams['page'] = this.currentPage;
    }
    if (this.fixedSorting === null && this.sorting && this.sorting !== 'title,asc') {
      queryParams['sorting'] = this.sorting;
    }
    this.router!.navigate([], {relativeTo: this.route!, queryParams});
  }

  protected onBeforeInit(): void {}

  private parsePageFromUrl(): number {
    const match = window.location.pathname.match(/\/page:(\d+)/);
    if (match) {
      const page = parseInt(match[1], 10);
      return page > 0 ? page : 1;
    }
    return 1;
  }

  private parseUrlBase(): string {
    const cleanPath = window.location.pathname.replace(/\/page:\d+\/?/, '');
    return cleanPath.endsWith('/') ? cleanPath : cleanPath + '/';
  }

  private updateUrl(page: number): void {
    const newPath = page > 1 ? this.urlBase + 'page:' + page + '/' : this.urlBase;
    window.history.pushState(null, '', newPath);
  }

  protected loadPage(): void {
    // elementId 0 = SPA collection mount: the backend resolves the catalogue root by type.
    this.loading = true;
    this.error = false;
    const start = (this.currentPage - 1) * this.itemsPerPage;
    this.fetchPage(start, this.itemsPerPage);
  }

  protected abstract fetchPage(start: number, limit: number): void;
}
