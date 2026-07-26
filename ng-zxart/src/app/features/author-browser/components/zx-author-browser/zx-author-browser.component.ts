import {ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, Input, OnDestroy, OnInit,} from '@angular/core';
import {ActivatedRoute, Params, Router} from '@angular/router';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
import {AuthorBrowserService} from '../../services/author-browser.service';
import {AuthorListItem} from '../../models/author-list-item';
import {AuthorFilterOption} from '../../models/author-filter-options';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {
  ZxFilterPickerComponent,
  ZxFilterPickerItem
} from '../../../../shared/ui/zx-filter-picker/zx-filter-picker.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxAuthorsTableComponent} from '../../../../entities/zx-authors-table/zx-authors-table.component';
import {
  ZxAuthorsTableSkeletonComponent
} from '../../../../entities/zx-authors-table-skeleton/zx-authors-table-skeleton.component';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxNavChipsComponent} from '../../../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {buildLetterChips, ZxNavChip} from '../../../../shared/ui/zx-nav-chips/nav-chip';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';

@Component({
  selector: 'zx-author-browser',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxPaginationComponent,
    TextDirective,
    ZxFilterPickerComponent,
    ZxInputComponent,
    ZxAuthorsTableComponent,
    ZxAuthorsTableSkeletonComponent,
    ZxFilterBarComponent,
    ZxStackComponent,
    ZxNavChipsComponent,
    HeadingDirective,
    InViewportDirective,
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
  /** Content type of the authors list: 'music', 'graphics', or '' (all) */
  @Input() items = '';
  /** Base route the browser builds letter, filter and pagination links against */
  @Input() basePath = '/authors';
  /** Defers the initial request until the browser enters the viewport. */
  @Input() loadOnViewport = false;

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

  loadStarted = false;

  private readonly subscriptions = new Subscription();
  private readonly searchSubject = new Subject<string>();
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  constructor(
    private readonly authorBrowserService: AuthorBrowserService,
    private readonly cdr: ChangeDetectorRef,
    private readonly translateService: TranslateService,
  ) {}

  ngOnInit(): void {
    // Full mode owns the page URL: letter, filters and page live in the route.
    if (this.mode === 'full') {
      this.subscriptions.add(
        this.searchSubject.pipe(
          debounceTime(300),
          distinctUntilChanged(),
        ).subscribe(() => this.navigateWithFilters()),
      );

      this.subscriptions.add(combineLatest([
        this.route.paramMap,
        this.route.queryParamMap,
      ]).subscribe(([pathParams, queryParams]) => {
        this.letter = pathParams.get('letter') ?? '';
        this.search = queryParams.get('q') ?? '';
        this.selectedCountryIds = queryParams.get('country') ? [queryParams.get('country')!] : [];
        this.selectedCityIds = queryParams.get('city') ? [queryParams.get('city')!] : [];
        this.currentPage = Math.max(1, Number(queryParams.get('page')) || 1);
        this.loadFilterOptions();
        this.loadPage();
      }));
      return;
    }

    // Simple mode is an embedded widget with a fixed query and no URL state.
    if (!this.loadOnViewport) {
      this.loadStarted = true;
      this.loadPage();
    }
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
    this.navigateWithFilters();
  }

  onCityChange(ids: string[]): void {
    this.selectedCityIds = ids;
    this.currentPage = 1;
    this.navigateWithFilters();
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.navigateWithFilters();
  }

  onInViewport(): void {
    if (!this.loadOnViewport || this.loadStarted) {
      return;
    }
    this.loadStarted = true;
    this.loadPage();
  }

  get letterChips(): ZxNavChip[] {
    return buildLetterChips(this.basePath, this.letter);
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

  get skeletonCount(): number {
    const pageLimit = Number(this.limit) || 50;
    return Math.min(pageLimit, 50);
  }

  /** No letter selected in full mode: list the most recently added authors instead of the full A–Z catalogue. */
  get isRecentView(): boolean {
    return this.mode === 'full' && !this.letter;
  }

  get effectiveSorting(): string {
    return this.isRecentView ? 'id,desc' : this.sorting;
  }

  /** The "recently added" heading belongs to the default listing only, not to search/filter results. */
  get showRecentHeading(): boolean {
    return this.isRecentView && !this.search && this.activeCountryId === null && this.activeCityId === null;
  }

  private loadPage(): void {
    // elementId 0 = SPA collection mount: the author list resolves globally on the backend.
    this.loading = true;
    this.error = false;
    const pageLimit = Number(this.limit) || 50;
    const start = (this.currentPage - 1) * pageLimit;

    this.subscriptions.add(
      this.authorBrowserService.getPaged(
        this.elementId,
        start,
        pageLimit,
        this.effectiveSorting,
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

  /** Writes the filter state to the URL; the query-param subscription reloads. */
  private navigateWithFilters(): void {
    this.router.navigate([], {relativeTo: this.route, queryParams: this.filterQueryParams});
  }

  /** Filter state carried by the pagination links. */
  get paginationQueryParams(): Params {
    const {page, ...withoutPage} = this.filterQueryParams;
    return withoutPage;
  }

  private get filterQueryParams(): Params {
    const params: Params = {};
    if (this.search) {
      params['q'] = this.search;
    }
    if (this.activeCountryId !== null) {
      params['country'] = this.activeCountryId;
    }
    if (this.activeCityId !== null) {
      params['city'] = this.activeCityId;
    }
    if (this.currentPage > 1) {
      params['page'] = this.currentPage;
    }
    return params;
  }
}
