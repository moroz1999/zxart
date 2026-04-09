import {ChangeDetectorRef, Directive, Input, OnDestroy, OnInit} from '@angular/core';
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

  protected constructor(
    protected readonly translateService: TranslateService,
    protected readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.onBeforeInit();
    this.urlBase = this.parseUrlBase();
    this.currentPage = this.parsePageFromUrl();
    this.subscriptions.add(
      this.translateService.stream(this.sortKeys.map(k => SORT_TRANSLATION_PREFIX + k)).subscribe(translations => {
        this.sortingOptions = this.sortKeys.map(k => ({
          value: k,
          label: translations[SORT_TRANSLATION_PREFIX + k] ?? k,
        }));
        this.cdr.markForCheck();
      })
    );
    this.loadPage();
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSortingChange(value: string): void {
    this.sorting = value;
    this.currentPage = 1;
    this.updateUrl(1);
    this.loadPage();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.updateUrl(page);
    this.loadPage();
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
    if (!this.elementId) {
      this.loading = false;
      this.error = true;
      this.cdr.markForCheck();
      return;
    }
    this.loading = true;
    this.error = false;
    const start = (this.currentPage - 1) * this.itemsPerPage;
    this.fetchPage(start, this.itemsPerPage);
  }

  protected abstract fetchPage(start: number, limit: number): void;
}
