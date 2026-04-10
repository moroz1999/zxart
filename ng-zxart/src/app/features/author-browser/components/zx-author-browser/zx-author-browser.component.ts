import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit,} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap, tap} from 'rxjs/operators';
import {AuthorBrowserService} from '../../services/author-browser.service';
import {AuthorListItem} from '../../models/author-list-item';
import {AuthorFilterOption} from '../../models/author-filter-options';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {
  ZxFilterPickerComponent,
  ZxFilterPickerItem
} from '../../../../shared/ui/zx-filter-picker/zx-filter-picker.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';

@Component({
  selector: 'zx-author-browser',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxPaginationComponent,
    ZxCaptionDirective,
    ZxFilterPickerComponent,
    ZxInputComponent,
    ZxTableComponent,
    ZxSkeletonComponent,
  ],
  templateUrl: './zx-author-browser.component.html',
  styleUrls: ['./zx-author-browser.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorBrowserComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  /** 'full' shows filters, pagination and URL state; 'simple' shows table only */
  @Input() mode: 'full' | 'simple' = 'full';
  /** Override default sorting (e.g. 'graphicsRating,desc' for best-authors list) */
  @Input() sorting = 'title,asc';
  /** Maximum items to load (as string to support custom-element string attributes) */
  @Input() limit = '50';
  /** When set, restricts results to authors starting with this letter (Latin↔Cyrillic mapped automatically) */
  @Input() letter = '';
  /** Comma-separated entity types to include: 'author', 'authorAlias' */
  @Input() types = '';
  /** Content type of the authors list: 'music', 'graphics', or 'all' */
  @Input() items = '';

  loading = true;
  error = false;
  authors: AuthorListItem[] | null = null;
  total = 0;
  currentPage = 1;
  pagesAmount = 0;

  search = '';
  selectedCountryIds: string[] = [];
  selectedCityIds: string[] = [];
  countryOptions: ZxFilterPickerItem[] = [];
  cityOptions: ZxFilterPickerItem[] = [];

  protected urlBase = '';

  private readonly subscriptions = new Subscription();
  private readonly searchSubject = new Subject<string>();

  constructor(
    private readonly authorBrowserService: AuthorBrowserService,
    private readonly cdr: ChangeDetectorRef,
    private readonly translateService: TranslateService,
  ) {}

  ngOnInit(): void {
    if (this.mode === 'full') {
      this.urlBase = this.parseUrlBase();
      this.currentPage = this.parsePageFromUrl();
      this.parseFiltersFromUrl();

      this.loadFilterOptions();

      this.subscriptions.add(
        this.searchSubject.pipe(
          debounceTime(300),
          distinctUntilChanged(),
          tap(() => {
            this.currentPage = 1;
            this.updateUrl();
            this.loading = true;
            this.error = false;
            this.cdr.markForCheck();
          }),
          switchMap(() => {
            const pageLimit = Number(this.limit) || 50;
            return this.authorBrowserService.getPaged(
              this.elementId,
              0,
              pageLimit,
              this.sorting,
              this.search,
              this.activeCountryId,
              this.activeCityId,
              this.letter,
              this.types,
              this.items,
            );
          }),
        ).subscribe({
          next: response => {
            this.loading = false;
            const pageLimit = Number(this.limit) || 50;
            this.authors = response.items;
            this.total = response.total;
            this.pagesAmount = Math.ceil(this.total / pageLimit);
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

    this.loadPage();
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSearchInput(value: string): void {
    this.search = value;
    if (this.mode === 'full') {
      this.searchSubject.next(value);
    }
  }

  onCountryChange(ids: string[]): void {
    this.selectedCountryIds = ids;
    this.currentPage = 1;
    this.updateUrl();
    this.loadPage();
  }

  onCityChange(ids: string[]): void {
    this.selectedCityIds = ids;
    this.currentPage = 1;
    this.updateUrl();
    this.loadPage();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.updateUrl();
    this.loadPage();
  }

  get activeCountryId(): number | null {
    return this.selectedCountryIds.length > 0 ? Number(this.selectedCountryIds[0]) : null;
  }

  get activeCityId(): number | null {
    return this.selectedCityIds.length > 0 ? Number(this.selectedCityIds[0]) : null;
  }

  get rowStartIndex(): number {
    const pageLimit = Number(this.limit) || 50;
    return (this.currentPage - 1) * pageLimit;
  }

  private loadPage(): void {
    if (!this.elementId) {
      this.loading = false;
      this.error = true;
      this.cdr.markForCheck();
      return;
    }

    this.loading = true;
    this.error = false;
    const pageLimit = Number(this.limit) || 50;
    const start = (this.currentPage - 1) * pageLimit;

    this.subscriptions.add(
      this.authorBrowserService.getPaged(
        this.elementId,
        start,
        pageLimit,
        this.sorting,
        this.mode === 'full' ? this.search : '',
        this.mode === 'full' ? this.activeCountryId : null,
        this.mode === 'full' ? this.activeCityId : null,
        this.letter,
        this.types,
        this.items,
      ).subscribe({
        next: response => {
          this.loading = false;
          this.authors = response.items;
          this.total = response.total;
          this.pagesAmount = Math.ceil(this.total / pageLimit);
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

  private loadFilterOptions(): void {
    this.subscriptions.add(
      this.authorBrowserService.getFilterOptions(this.elementId, this.letter, this.items).subscribe(options => {
        const locale = this.translateService.currentLang ?? undefined;
        this.countryOptions = options.countries
          .map((c: AuthorFilterOption) => ({id: String(c.id), label: c.title}))
          .sort((a, b) => a.label.localeCompare(b.label, locale));
        this.cityOptions = options.cities
          .map((c: AuthorFilterOption) => ({id: String(c.id), label: c.title}))
          .sort((a, b) => a.label.localeCompare(b.label, locale));
        this.cdr.markForCheck();
      }),
    );
  }

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

  private parseFiltersFromUrl(): void {
    const params = new URLSearchParams(window.location.search);
    this.search = params.get('q') ?? '';
    const countryId = params.get('country');
    const cityId = params.get('city');
    this.selectedCountryIds = countryId ? [countryId] : [];
    this.selectedCityIds = cityId ? [cityId] : [];
  }

  private updateUrl(): void {
    if (this.mode !== 'full') {
      return;
    }
    const pagePath = this.currentPage > 1
      ? this.urlBase + 'page:' + this.currentPage + '/'
      : this.urlBase;

    const params = new URLSearchParams();
    if (this.search) {
      params.set('q', this.search);
    }
    if (this.activeCountryId !== null) {
      params.set('country', String(this.activeCountryId));
    }
    if (this.activeCityId !== null) {
      params.set('city', String(this.activeCityId));
    }

    const queryString = params.toString();
    const newUrl = queryString ? pagePath + '?' + queryString : pagePath;
    window.history.pushState(null, '', newUrl);
  }
}
