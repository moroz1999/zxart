import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BreakpointObserver} from '@angular/cdk/layout';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {Observable, of, Subject, Subscription} from 'rxjs';
import {catchError, debounceTime, distinctUntilChanged, map, switchMap, tap} from 'rxjs/operators';
import {ZxBreakpoints} from '../../../../shared/breakpoints';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {TagItem} from '../../../../shared/models/tag-item';
import {TagsSearchService} from '../../../../shared/services/tags-search.service';
import {AuthorListItem} from '../../../author-browser/models/author-list-item';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureSearchApiService} from '../../services/picture-search-api.service';
import {LocationSearchService} from '../../services/location-search.service';
import {
  countActivePictureSearchFilters,
  createDefaultPictureSearchFilters,
  PictureSearchFilters,
  PictureSearchResultsType,
  PictureSearchSortOrder,
} from '../../models/picture-search-filters';
import {buildPictureSearchPath, parsePictureSearchUrl} from '../../models/picture-search-url';
import {PictureSearchLocation} from '../../models/picture-search-response';
import {ZX_PICTURE_TYPES} from '../../models/zx-picture-types';
import {ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxSelectComponent} from '../../../../shared/ui/zx-select/zx-select.component';
import {ZxCheckboxFieldComponent} from '../../../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxTagsInputComponent} from '../../../../shared/ui/zx-tags-input/zx-tags-input.component';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxFormDirective} from '../../../../shared/ui/zx-form/zx-form.directive';
import {ZxFormSectionComponent} from '../../../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxFormFieldsetComponent} from '../../../../shared/ui/zx-form/zx-form-fieldset/zx-form-fieldset.component';
import {ZxFormFieldComponent} from '../../../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormControlComponent} from '../../../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormActionsComponent} from '../../../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ZxPictureCardComponent} from '../../../../entities/zx-picture-card/zx-picture-card.component';
import {
  ZxPictureCardSkeletonComponent
} from '../../../../entities/zx-picture-card-skeleton/zx-picture-card-skeleton.component';
import {ZxAuthorsTableComponent} from '../../../../entities/zx-authors-table/zx-authors-table.component';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {
  PictureGalleryHostComponent
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';

const ELEMENTS_ON_PAGE = 60;
const SEARCH_DEBOUNCE_MS = 250;

@Component({
  selector: 'zx-picture-search',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxBadgeComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxGridComponent,
    ZxInputComponent,
    ZxSelectComponent,
    ZxCheckboxFieldComponent,
    ZxTagsInputComponent,
    ZxPaginationComponent,
    ZxFormDirective,
    ZxFormSectionComponent,
    ZxFormFieldsetComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormControlComponent,
    ZxFormActionsComponent,
    TextDirective,
    HeadingDirective,
    ZxPictureCardComponent,
    ZxPictureCardSkeletonComponent,
    ZxAuthorsTableComponent,
    ZxPicturesGridDirective,
    PictureGalleryHostComponent,
  ],
  templateUrl: './zx-picture-search.component.html',
  styleUrls: ['./zx-picture-search.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureSearchComponent implements OnInit, OnDestroy {
  filters: PictureSearchFilters = createDefaultPictureSearchFilters();

  loading = true;
  error = false;
  totalAmount = 0;
  currentPage = 1;
  pagesAmount = 0;
  resultsType: PictureSearchResultsType = 'zxitem';
  pictures: ZxPictureDto[] = [];
  authors: AuthorListItem[] = [];
  apiUrl = '';
  zipUrl = '';

  tagsIncludeItems: TagItem[] = [];
  tagsExcludeItems: TagItem[] = [];
  countryItems: TagItem[] = [];
  cityItems: TagItem[] = [];

  tagsIncludeResults: TagItem[] = [];
  tagsExcludeResults: TagItem[] = [];
  countryResults: TagItem[] = [];
  cityResults: TagItem[] = [];
  tagsIncludeLoading = false;
  tagsExcludeLoading = false;
  countryLoading = false;
  cityLoading = false;

  isMobile = false;
  filtersCollapsed = false;

  pictureTypeOptions: ZxSelectOption[] = [];
  ratingOptions: ZxSelectOption[] = [];
  sortOptions: ZxSelectOption[] = [];

  readonly galleryId = 'picture-search';
  readonly skeletonItems = [0, 1, 2, 3, 4, 5];

  protected urlBase = '/';
  private appliedFilters: PictureSearchFilters = createDefaultPictureSearchFilters();

  private readonly subscriptions = new Subscription();
  private readonly tagsIncludeQuery = new Subject<string>();
  private readonly tagsExcludeQuery = new Subject<string>();
  private readonly countryQuery = new Subject<string>();
  private readonly cityQuery = new Subject<string>();

  constructor(
    private readonly api: PictureSearchApiService,
    private readonly locationSearch: LocationSearchService,
    private readonly tagsSearch: TagsSearchService,
    private readonly galleryService: PictureGalleryService,
    private readonly translateService: TranslateService,
    private readonly breakpointObserver: BreakpointObserver,
    private readonly iconRegistry: SvgIconRegistryService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.loadIcons();
    const parsed = parsePictureSearchUrl(window.location.pathname);
    this.urlBase = parsed.urlBase;
    this.filters = parsed.filters;
    this.appliedFilters = {...parsed.filters};
    this.currentPage = parsed.page;

    this.tagsIncludeItems = this.filters.tagsInclude.map(title => this.toTagItem(title));
    this.tagsExcludeItems = this.filters.tagsExclude.map(title => this.toTagItem(title));
    this.restoreLocations();

    this.observeMobile();
    this.buildStaticOptions();
    this.connectTagSearch(this.tagsIncludeQuery, results => {
      this.tagsIncludeResults = results;
      this.tagsIncludeLoading = false;
    }, () => this.tagsIncludeLoading = true);
    this.connectTagSearch(this.tagsExcludeQuery, results => {
      this.tagsExcludeResults = results;
      this.tagsExcludeLoading = false;
    }, () => this.tagsExcludeLoading = true);
    this.connectLocationSearch(this.countryQuery, query => this.locationSearch.searchCountries(query), results => {
      this.countryResults = results;
      this.countryLoading = false;
    }, () => this.countryLoading = true);
    this.connectLocationSearch(this.cityQuery, query => this.locationSearch.searchCities(query), results => {
      this.cityResults = results;
      this.cityLoading = false;
    }, () => this.cityLoading = true);

    this.load();
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  get activeFiltersCount(): number {
    return countActivePictureSearchFilters(this.filters);
  }

  get paginationUrlBase(): string {
    return buildPictureSearchPath(this.urlBase, this.appliedFilters, 1);
  }

  get rangeStart(): number {
    if (this.totalAmount === 0) {
      return 0;
    }
    return (this.currentPage - 1) * ELEMENTS_ON_PAGE + 1;
  }

  get rangeEnd(): number {
    return Math.min(this.currentPage * ELEMENTS_ON_PAGE, this.totalAmount);
  }

  onSubmit(): void {
    this.appliedFilters = {...this.filters};
    this.currentPage = 1;
    this.updateUrl();
    this.load();
  }

  onReset(): void {
    this.filters = createDefaultPictureSearchFilters();
    this.tagsIncludeItems = [];
    this.tagsExcludeItems = [];
    this.countryItems = [];
    this.cityItems = [];
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.updateUrl();
    this.load();
  }

  toggleFilters(): void {
    this.filtersCollapsed = !this.filtersCollapsed;
  }

  setResultsType(resultsType: PictureSearchResultsType): void {
    this.filters.resultsType = resultsType;
  }

  setSortOrder(sortOrder: PictureSearchSortOrder): void {
    this.filters.sortOrder = sortOrder;
  }

  onTagsIncludeQuery(query: string): void {
    this.tagsIncludeQuery.next(query);
  }

  onTagsExcludeQuery(query: string): void {
    this.tagsExcludeQuery.next(query);
  }

  onCountryQuery(query: string): void {
    this.countryQuery.next(query);
  }

  onCityQuery(query: string): void {
    this.cityQuery.next(query);
  }

  onTagIncludeAdded(tag: TagItem): void {
    this.tagsIncludeItems = this.appendUniqueTitle(this.tagsIncludeItems, tag);
    this.filters.tagsInclude = this.tagsIncludeItems.map(item => item.title);
  }

  onTagIncludeCustom(title: string): void {
    this.onTagIncludeAdded(this.toTagItem(title));
  }

  onTagIncludeRemoved(tag: TagItem): void {
    this.tagsIncludeItems = this.tagsIncludeItems.filter(item => item.title !== tag.title);
    this.filters.tagsInclude = this.tagsIncludeItems.map(item => item.title);
  }

  onTagExcludeAdded(tag: TagItem): void {
    this.tagsExcludeItems = this.appendUniqueTitle(this.tagsExcludeItems, tag);
    this.filters.tagsExclude = this.tagsExcludeItems.map(item => item.title);
  }

  onTagExcludeCustom(title: string): void {
    this.onTagExcludeAdded(this.toTagItem(title));
  }

  onTagExcludeRemoved(tag: TagItem): void {
    this.tagsExcludeItems = this.tagsExcludeItems.filter(item => item.title !== tag.title);
    this.filters.tagsExclude = this.tagsExcludeItems.map(item => item.title);
  }

  onCountryAdded(tag: TagItem): void {
    this.countryItems = this.appendUniqueId(this.countryItems, tag);
    this.filters.authorCountryIds = this.toIdList(this.countryItems);
  }

  onCountryRemoved(tag: TagItem): void {
    this.countryItems = this.countryItems.filter(item => item.id !== tag.id);
    this.filters.authorCountryIds = this.toIdList(this.countryItems);
  }

  onCityAdded(tag: TagItem): void {
    this.cityItems = this.appendUniqueId(this.cityItems, tag);
    this.filters.authorCityIds = this.toIdList(this.cityItems);
  }

  onCityRemoved(tag: TagItem): void {
    this.cityItems = this.cityItems.filter(item => item.id !== tag.id);
    this.filters.authorCityIds = this.toIdList(this.cityItems);
  }

  private load(): void {
    this.loading = true;
    this.error = false;
    this.cdr.markForCheck();
    const start = (this.currentPage - 1) * ELEMENTS_ON_PAGE;
    this.subscriptions.add(
      this.api.search(this.appliedFilters, start, ELEMENTS_ON_PAGE).subscribe(response => {
        this.loading = false;
        if (response === null) {
          this.error = true;
          this.cdr.markForCheck();
          return;
        }
        this.totalAmount = response.totalAmount;
        this.resultsType = response.resultsType;
        this.pictures = response.pictures;
        this.authors = response.authors;
        this.apiUrl = response.apiUrl;
        this.zipUrl = response.zipUrl;
        this.pagesAmount = Math.ceil(response.totalAmount / ELEMENTS_ON_PAGE);
        this.galleryService.ensureGalleryLoaded(this.galleryId, response.pictures);
        this.cdr.markForCheck();
      }),
    );
  }

  private updateUrl(): void {
    const newPath = buildPictureSearchPath(this.urlBase, this.appliedFilters, this.currentPage);
    window.history.pushState(null, '', newPath);
  }

  private restoreLocations(): void {
    const countryIds = this.filters.authorCountryIds;
    const cityIds = this.filters.authorCityIds;
    if (countryIds.length === 0 && cityIds.length === 0) {
      return;
    }
    this.subscriptions.add(
      this.api.resolveLocations([...countryIds, ...cityIds]).subscribe(locations => {
        this.countryItems = this.pickLocations(locations, countryIds);
        this.cityItems = this.pickLocations(locations, cityIds);
        this.cdr.markForCheck();
      }),
    );
  }

  private pickLocations(locations: PictureSearchLocation[], ids: number[]): TagItem[] {
    return locations
      .filter(location => ids.includes(location.id))
      .map(location => ({id: location.id, title: location.title, description: null}));
  }

  private loadIcons(): void {
    for (const icon of ['settings', 'expand-more', 'expand-less', 'open-in-new', 'download', 'search']) {
      this.iconRegistry.loadSvg(`${environment.svgUrl}${icon}.svg`, icon)?.subscribe();
    }
  }

  private observeMobile(): void {
    this.subscriptions.add(
      this.breakpointObserver.observe(ZxBreakpoints.MobileAll).subscribe(state => {
        if (this.isMobile === state.matches) {
          return;
        }
        this.isMobile = state.matches;
        this.filtersCollapsed = state.matches;
        this.cdr.markForCheck();
      }),
    );
  }

  private buildStaticOptions(): void {
    const formatKeys = ZX_PICTURE_TYPES.map(type => `picture-search.format.${type}`);
    const sortKeys = ['date', 'year', 'title', 'place', 'votes', 'commentsAmount', 'views']
      .map(parameter => `picture-search.sort.${parameter}`);
    const staticKeys = ['picture-search.all-types', 'picture-search.any-rating'];
    this.subscriptions.add(
      this.translateService.stream([...formatKeys, ...sortKeys, ...staticKeys]).subscribe(translations => {
        this.pictureTypeOptions = [
          {value: '', label: translations['picture-search.all-types']},
          ...ZX_PICTURE_TYPES.map(type => ({
            value: type,
            label: translations[`picture-search.format.${type}`] ?? type,
          })),
        ];
        this.ratingOptions = [
          {value: '', label: translations['picture-search.any-rating']},
          {value: '1', label: '★ 1+'},
          {value: '2', label: '★ 2+'},
          {value: '3', label: '★ 3+'},
          {value: '4', label: '★ 4+'},
          {value: '5', label: '★ 5'},
        ];
        this.sortOptions = ['date', 'year', 'title', 'place', 'votes', 'commentsAmount', 'views'].map(parameter => ({
          value: parameter,
          label: translations[`picture-search.sort.${parameter}`] ?? parameter,
        }));
        this.cdr.markForCheck();
      }),
    );
  }

  private connectTagSearch(
    query$: Subject<string>,
    apply: (results: TagItem[]) => void,
    markLoading: () => void,
  ): void {
    this.subscriptions.add(
      query$.pipe(
        debounceTime(SEARCH_DEBOUNCE_MS),
        distinctUntilChanged(),
        switchMap(query => {
          const normalized = query.trim();
          if (normalized === '') {
            return of([] as TagItem[]);
          }
          markLoading();
          this.cdr.markForCheck();
          return this.tagsSearch.search(normalized).pipe(
            map(tags => tags.map(tag => ({id: tag.id, title: tag.title, description: null} as TagItem))),
            catchError(() => of([] as TagItem[])),
          );
        }),
      ).subscribe(results => {
        apply(results);
        this.cdr.markForCheck();
      }),
    );
  }

  private connectLocationSearch(
    query$: Subject<string>,
    search: (query: string) => Observable<PictureSearchLocation[]>,
    apply: (results: TagItem[]) => void,
    markLoading: () => void,
  ): void {
    this.subscriptions.add(
      query$.pipe(
        debounceTime(SEARCH_DEBOUNCE_MS),
        distinctUntilChanged(),
        switchMap(query => {
          const normalized = query.trim();
          if (normalized === '') {
            return of([] as PictureSearchLocation[]);
          }
          markLoading();
          this.cdr.markForCheck();
          return search(normalized);
        }),
        map(locations => locations.map(location => ({
          id: location.id,
          title: location.title,
          description: null,
        } as TagItem))),
      ).subscribe(results => {
        apply(results);
        this.cdr.markForCheck();
      }),
    );
  }

  private toTagItem(title: string): TagItem {
    return {id: null, title, description: null};
  }

  private appendUniqueTitle(items: TagItem[], tag: TagItem): TagItem[] {
    const normalized = tag.title.trim().toLocaleLowerCase();
    if (items.some(item => item.title.trim().toLocaleLowerCase() === normalized)) {
      return items;
    }
    return [...items, tag];
  }

  private appendUniqueId(items: TagItem[], tag: TagItem): TagItem[] {
    if (tag.id === null || items.some(item => item.id === tag.id)) {
      return items;
    }
    return [...items, tag];
  }

  private toIdList(items: TagItem[]): number[] {
    return items
      .map(item => item.id)
      .filter((id): id is number => id !== null);
  }
}
