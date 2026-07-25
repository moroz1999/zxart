import {ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, Input, OnDestroy, OnInit,} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged, switchMap, tap} from 'rxjs/operators';
import {GroupBrowserService} from '../../services/group-browser.service';
import {GroupListItem} from '../../models/group-list-item';
import {GroupFilterOption} from '../../models/group-filter-options';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {
  ZxFilterPickerComponent,
  ZxFilterPickerItem
} from '../../../../shared/ui/zx-filter-picker/zx-filter-picker.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxGroupsTableComponent} from '../../../../entities/zx-groups-table/zx-groups-table.component';
import {
  ZxGroupsTableSkeletonComponent
} from '../../../../entities/zx-groups-table-skeleton/zx-groups-table-skeleton.component';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxNavChipsComponent} from '../../../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {buildLetterChips, ZxNavChip} from '../../../../shared/ui/zx-nav-chips/nav-chip';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';

@Component({
  selector: 'zx-group-browser',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxPaginationComponent,
    TextDirective,
    ZxFilterPickerComponent,
    ZxInputComponent,
    ZxGroupsTableComponent,
    ZxGroupsTableSkeletonComponent,
    ZxFilterBarComponent,
    ZxStackComponent,
    ZxNavChipsComponent,
    HeadingDirective,
  ],
  templateUrl: './zx-group-browser.component.html',
  styleUrls: ['./zx-group-browser.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupBrowserComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  /** Legacy mounts sync state to `window.location`/`pushState`; the SPA route disables that. */
  @Input() manageUrl = true;
  @Input() mode: 'full' | 'simple' = 'full';
  @Input() sorting = 'title,asc';
  @Input() limit = '50';
  @Input() letter = '';
  @Input() types = '';
  @Input() groupType = '';
  /** Base route the browser builds letter and pagination links against */
  @Input() basePath = '/groups';

  loading = true;
  error = false;
  groups: GroupListItem[] | null = null;
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
  private readonly route = inject(ActivatedRoute, {optional: true});

  constructor(
    private readonly groupBrowserService: GroupBrowserService,
    private readonly cdr: ChangeDetectorRef,
    private readonly translateService: TranslateService,
  ) {}

  /** SPA route mode: the letter comes from the router; `manageUrl` is disabled. */
  private get useRouter(): boolean {
    return !this.manageUrl && this.route != null;
  }

  ngOnInit(): void {
    if (this.mode === 'full') {
      if (this.manageUrl) {
        this.urlBase = this.parseUrlBase();
        this.currentPage = this.parsePageFromUrl();
        this.parseFiltersFromUrl();
      }

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
            return this.groupBrowserService.getPaged(
              this.elementId,
              0,
              pageLimit,
              this.effectiveSorting,
              this.search,
              this.activeCountryId,
              this.activeCityId,
              this.letter,
              this.types,
              this.groupType,
            );
          }),
        ).subscribe({
          next: response => {
            this.loading = false;
            const pageLimit = Number(this.limit) || 50;
            this.groups = response.items;
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

      if (this.useRouter) {
        this.subscriptions.add(combineLatest([
          this.route!.paramMap,
          this.route!.queryParamMap,
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

      this.loadFilterOptions();
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

  get skeletonCount(): number {
    return Number(this.limit) || 50;
  }

  get letterChips(): ZxNavChip[] {
    return buildLetterChips(this.basePath, this.letter);
  }

  /** No letter selected in full mode: list the most recently added groups instead of the full A–Z catalogue. */
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
    // elementId 0 = SPA collection mount: the group list resolves globally on the backend.
    this.loading = true;
    this.error = false;
    const pageLimit = Number(this.limit) || 50;
    const start = (this.currentPage - 1) * pageLimit;

    this.subscriptions.add(
      this.groupBrowserService.getPaged(
        this.elementId,
        start,
        pageLimit,
        this.effectiveSorting,
        this.mode === 'full' ? this.search : '',
        this.mode === 'full' ? this.activeCountryId : null,
        this.mode === 'full' ? this.activeCityId : null,
        this.letter,
        this.types,
        this.groupType,
      ).subscribe({
        next: response => {
          this.loading = false;
          this.groups = response.items;
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
      this.groupBrowserService.getFilterOptions(this.elementId, this.letter, this.groupType).subscribe(options => {
        const locale = this.translateService.currentLang ?? undefined;
        this.countryOptions = options.countries
          .map((c: GroupFilterOption) => ({id: String(c.id), label: c.title}))
          .sort((a, b) => a.label.localeCompare(b.label, locale));
        this.cityOptions = options.cities
          .map((c: GroupFilterOption) => ({id: String(c.id), label: c.title}))
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
    if (this.mode !== 'full' || !this.manageUrl) {
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
