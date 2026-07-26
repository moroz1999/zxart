import {ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, Input, OnDestroy, OnInit,} from '@angular/core';
import {ActivatedRoute, Params, Router} from '@angular/router';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, Subject, Subscription} from 'rxjs';
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
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

  private readonly subscriptions = new Subscription();
  private readonly searchSubject = new Subject<string>();
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  constructor(
    private readonly groupBrowserService: GroupBrowserService,
    private readonly cdr: ChangeDetectorRef,
    private readonly translateService: TranslateService,
  ) {}

  ngOnInit(): void {
    // Simple mode is an embedded widget with a fixed query: it neither reads nor
    // writes the URL, so it loads its single page once.
    if (this.mode !== 'full') {
      this.loadPage();
      return;
    }

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
