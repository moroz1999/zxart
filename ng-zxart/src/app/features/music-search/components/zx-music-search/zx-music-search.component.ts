import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BreakpointObserver} from '@angular/cdk/layout';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {BehaviorSubject, combineLatest, Observable, of, Subject} from 'rxjs';
import {
  catchError,
  debounceTime,
  distinctUntilChanged,
  filter,
  map,
  shareReplay,
  startWith,
  switchMap,
} from 'rxjs/operators';
import {ZxBreakpoints} from '../../../../shared/breakpoints';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {TagItem} from '../../../../shared/models/tag-item';
import {TagsSearchService} from '../../../../shared/services/tags-search.service';
import {AuthorListItem} from '../../../author-browser/models/author-list-item';
import {PlayerService} from '../../../player/services/player.service';
import {LocationSearchService} from '../../../picture-search/services/location-search.service';
import {MusicSearchApiService} from '../../services/music-search-api.service';
import {
  countActiveMusicSearchFilters,
  createDefaultMusicSearchFilters,
  MUSIC_SEARCH_FORMAT_GROUPS,
  MUSIC_SEARCH_SORT_PARAMETERS,
  MusicSearchFilters,
  MusicSearchResultsType,
  MusicSearchSortOrder,
} from '../../models/music-search-filters';
import {buildMusicSearchPath, parseMusicSearchUrl} from '../../models/music-search-url';
import {MusicSearchLocation, MusicSearchResponse} from '../../models/music-search-response';
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
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {
  ZxTuneTableSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';
import {ZxFormDirective} from '../../../../shared/ui/zx-form/zx-form.directive';
import {ZxFormSectionComponent} from '../../../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxFormFieldsetComponent} from '../../../../shared/ui/zx-form/zx-form-fieldset/zx-form-fieldset.component';
import {ZxFormFieldComponent} from '../../../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormControlComponent} from '../../../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormActionsComponent} from '../../../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ZxAuthorsTableComponent} from '../../../../entities/zx-authors-table/zx-authors-table.component';

const ELEMENTS_ON_PAGE = 60;
const SEARCH_DEBOUNCE_MS = 250;
const PLAYLIST_ID = 'music-search';

interface MusicSearchRequest {
  filters: MusicSearchFilters;
  page: number;
}

interface SearchState {
  loading: boolean;
  error: boolean;
  totalAmount: number;
  currentPage: number;
  pagesAmount: number;
  resultsType: MusicSearchResultsType;
  tunes: ZxTuneDto[];
  authors: AuthorListItem[];
  formats: string[];
  apiUrl: string;
  zipUrl: string;
}

interface TagsSearchState {
  results: TagItem[];
  loading: boolean;
}

interface SearchOptionsState {
  formatOptions: ZxSelectOption[];
  formatGroupOptions: ZxSelectOption[];
  ratingOptions: ZxSelectOption[];
  sortOptions: ZxSelectOption[];
}

interface MusicSearchVm extends SearchState, SearchOptionsState {
  isMobile: boolean;
  filtersCollapsed: boolean;
  rangeStart: number;
  rangeEnd: number;
  paginationUrlBase: string;
  tagsIncludeItems: TagItem[];
  tagsExcludeItems: TagItem[];
  countryItems: TagItem[];
  cityItems: TagItem[];
  tagsIncludeSearch: TagsSearchState;
  tagsExcludeSearch: TagsSearchState;
  countrySearch: TagsSearchState;
  citySearch: TagsSearchState;
}

const EMPTY_SEARCH_STATE: SearchState = {
  loading: true,
  error: false,
  totalAmount: 0,
  currentPage: 1,
  pagesAmount: 0,
  resultsType: 'zxitem',
  tunes: [],
  authors: [],
  formats: [],
  apiUrl: '',
  zipUrl: '',
};

const EMPTY_TAGS_SEARCH_STATE: TagsSearchState = {
  results: [],
  loading: false,
};

@Component({
  selector: 'zx-music-search',
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
    ZxTableComponent,
    ZxTuneRowComponent,
    ZxTuneTableSkeletonComponent,
    ZxFormDirective,
    ZxFormSectionComponent,
    ZxFormFieldsetComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormControlComponent,
    ZxFormActionsComponent,
    TextDirective,
    HeadingDirective,
    ZxAuthorsTableComponent,
  ],
  templateUrl: './zx-music-search.component.html',
  styleUrls: ['./zx-music-search.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxMusicSearchComponent implements OnInit {
  filters: MusicSearchFilters = createDefaultMusicSearchFilters();

  readonly playingTuneId$ = this.playerService.state$.pipe(
    map(state => state.isPlaying && state.playlistId === PLAYLIST_ID
      ? (state.playlist[state.currentIndex]?.id ?? null)
      : null
    )
  );

  readonly vm$: Observable<MusicSearchVm>;

  protected urlBase = '/';
  private appliedFilters: MusicSearchFilters = createDefaultMusicSearchFilters();
  private currentPage = 1;

  private readonly requestStore = new BehaviorSubject<MusicSearchRequest | null>(null);
  private readonly filtersCollapsedStore = new BehaviorSubject<boolean | null>(null);
  private readonly tagsIncludeItemsStore = new BehaviorSubject<TagItem[]>([]);
  private readonly tagsExcludeItemsStore = new BehaviorSubject<TagItem[]>([]);
  private readonly manualCountryItemsStore = new BehaviorSubject<TagItem[]>([]);
  private readonly manualCityItemsStore = new BehaviorSubject<TagItem[]>([]);
  private readonly removedCountryIdsStore = new BehaviorSubject<number[]>([]);
  private readonly removedCityIdsStore = new BehaviorSubject<number[]>([]);
  private readonly restoredLocationIdsStore = new BehaviorSubject<{countryIds: number[]; cityIds: number[]}>({
    countryIds: [],
    cityIds: [],
  });

  private readonly tagsIncludeQuery = new Subject<string>();
  private readonly tagsExcludeQuery = new Subject<string>();
  private readonly countryQuery = new Subject<string>();
  private readonly cityQuery = new Subject<string>();

  constructor(
    private readonly api: MusicSearchApiService,
    private readonly locationSearch: LocationSearchService,
    private readonly tagsSearch: TagsSearchService,
    private readonly playerService: PlayerService,
    private readonly translateService: TranslateService,
    private readonly breakpointObserver: BreakpointObserver,
    private readonly iconRegistry: SvgIconRegistryService,
  ) {
    const searchState$ = this.createSearchState();
    const restoredLocations$ = this.createRestoredLocations();
    const countryItems$ = this.createLocationItems(
      restoredLocations$.pipe(map(({countries}) => countries)),
      this.manualCountryItemsStore,
      this.removedCountryIdsStore,
    );
    const cityItems$ = this.createLocationItems(
      restoredLocations$.pipe(map(({cities}) => cities)),
      this.manualCityItemsStore,
      this.removedCityIdsStore,
    );

    this.vm$ = combineLatest([
      searchState$,
      this.createOptionsState(searchState$),
      this.createMobileState(),
      this.filtersCollapsedStore,
      this.tagsIncludeItemsStore,
      this.tagsExcludeItemsStore,
      countryItems$,
      cityItems$,
      this.createTagsSearchState(this.tagsIncludeQuery, query => this.tagsSearch.search(query).pipe(
        map(tags => tags.map(tag => ({id: tag.id, title: tag.title, description: null} as TagItem))),
      )),
      this.createTagsSearchState(this.tagsExcludeQuery, query => this.tagsSearch.search(query).pipe(
        map(tags => tags.map(tag => ({id: tag.id, title: tag.title, description: null} as TagItem))),
      )),
      this.createTagsSearchState(this.countryQuery, query => this.locationSearch.searchCountries(query).pipe(
        map(locations => this.locationsToTags(locations)),
      )),
      this.createTagsSearchState(this.cityQuery, query => this.locationSearch.searchCities(query).pipe(
        map(locations => this.locationsToTags(locations)),
      )),
    ]).pipe(
      map(([
        searchState,
        options,
        isMobile,
        collapsedOverride,
        tagsIncludeItems,
        tagsExcludeItems,
        countryItems,
        cityItems,
        tagsIncludeSearch,
        tagsExcludeSearch,
        countrySearch,
        citySearch,
      ]) => ({
        ...searchState,
        ...options,
        isMobile,
        filtersCollapsed: collapsedOverride ?? isMobile,
        rangeStart: this.getRangeStart(searchState),
        rangeEnd: this.getRangeEnd(searchState),
        paginationUrlBase: buildMusicSearchPath(this.urlBase, this.appliedFilters, 1),
        tagsIncludeItems,
        tagsExcludeItems,
        countryItems,
        cityItems,
        tagsIncludeSearch,
        tagsExcludeSearch,
        countrySearch,
        citySearch,
      })),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  ngOnInit(): void {
    this.loadIcons();
    const parsed = parseMusicSearchUrl(window.location.pathname);
    this.urlBase = parsed.urlBase;
    this.filters = parsed.filters;
    this.appliedFilters = {...parsed.filters};
    this.currentPage = parsed.page;

    this.tagsIncludeItemsStore.next(this.filters.tagsInclude.map(title => this.toTagItem(title)));
    this.tagsExcludeItemsStore.next(this.filters.tagsExclude.map(title => this.toTagItem(title)));
    this.restoredLocationIdsStore.next({
      countryIds: this.filters.authorCountryIds,
      cityIds: this.filters.authorCityIds,
    });
    this.requestStore.next({filters: {...this.appliedFilters}, page: this.currentPage});
  }

  get activeFiltersCount(): number {
    return countActiveMusicSearchFilters(this.filters);
  }

  onSubmit(): void {
    this.appliedFilters = {...this.filters};
    this.currentPage = 1;
    this.updateUrl();
    this.requestStore.next({filters: {...this.appliedFilters}, page: this.currentPage});
  }

  onReset(): void {
    this.filters = createDefaultMusicSearchFilters();
    this.tagsIncludeItemsStore.next([]);
    this.tagsExcludeItemsStore.next([]);
    this.manualCountryItemsStore.next([]);
    this.manualCityItemsStore.next([]);
    this.removedCountryIdsStore.next([]);
    this.removedCityIdsStore.next([]);
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.updateUrl();
    this.requestStore.next({filters: {...this.appliedFilters}, page: this.currentPage});
  }

  toggleFilters(currentValue: boolean): void {
    this.filtersCollapsedStore.next(!currentValue);
  }

  setResultsType(resultsType: MusicSearchResultsType): void {
    this.filters.resultsType = resultsType;
  }

  setSortOrder(sortOrder: MusicSearchSortOrder): void {
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
    const items = this.appendUniqueTitle(this.tagsIncludeItemsStore.getValue(), tag);
    this.tagsIncludeItemsStore.next(items);
    this.filters.tagsInclude = items.map(item => item.title);
  }

  onTagIncludeCustom(title: string): void {
    this.onTagIncludeAdded(this.toTagItem(title));
  }

  onTagIncludeRemoved(tag: TagItem): void {
    const items = this.tagsIncludeItemsStore.getValue().filter(item => item.title !== tag.title);
    this.tagsIncludeItemsStore.next(items);
    this.filters.tagsInclude = items.map(item => item.title);
  }

  onTagExcludeAdded(tag: TagItem): void {
    const items = this.appendUniqueTitle(this.tagsExcludeItemsStore.getValue(), tag);
    this.tagsExcludeItemsStore.next(items);
    this.filters.tagsExclude = items.map(item => item.title);
  }

  onTagExcludeCustom(title: string): void {
    this.onTagExcludeAdded(this.toTagItem(title));
  }

  onTagExcludeRemoved(tag: TagItem): void {
    const items = this.tagsExcludeItemsStore.getValue().filter(item => item.title !== tag.title);
    this.tagsExcludeItemsStore.next(items);
    this.filters.tagsExclude = items.map(item => item.title);
  }

  onCountryAdded(tag: TagItem): void {
    if (tag.id === null || this.filters.authorCountryIds.includes(tag.id)) {
      return;
    }
    this.filters.authorCountryIds = [...this.filters.authorCountryIds, tag.id];
    this.manualCountryItemsStore.next(this.appendUniqueId(this.manualCountryItemsStore.getValue(), tag));
    this.removedCountryIdsStore.next(this.removedCountryIdsStore.getValue().filter(id => id !== tag.id));
  }

  onCountryRemoved(tag: TagItem): void {
    if (tag.id === null) {
      return;
    }
    this.filters.authorCountryIds = this.filters.authorCountryIds.filter(id => id !== tag.id);
    this.manualCountryItemsStore.next(this.manualCountryItemsStore.getValue().filter(item => item.id !== tag.id));
    this.removedCountryIdsStore.next([...this.removedCountryIdsStore.getValue(), tag.id]);
  }

  onCityAdded(tag: TagItem): void {
    if (tag.id === null || this.filters.authorCityIds.includes(tag.id)) {
      return;
    }
    this.filters.authorCityIds = [...this.filters.authorCityIds, tag.id];
    this.manualCityItemsStore.next(this.appendUniqueId(this.manualCityItemsStore.getValue(), tag));
    this.removedCityIdsStore.next(this.removedCityIdsStore.getValue().filter(id => id !== tag.id));
  }

  onCityRemoved(tag: TagItem): void {
    if (tag.id === null) {
      return;
    }
    this.filters.authorCityIds = this.filters.authorCityIds.filter(id => id !== tag.id);
    this.manualCityItemsStore.next(this.manualCityItemsStore.getValue().filter(item => item.id !== tag.id));
    this.removedCityIdsStore.next([...this.removedCityIdsStore.getValue(), tag.id]);
  }

  playTune(tunes: ZxTuneDto[], index: number): void {
    const selected = tunes[index];
    if (!selected) {
      return;
    }
    const playable = tunes.filter(tune => tune.isPlayable && tune.mp3Url);
    const startIndex = playable.findIndex(tune => tune.id === selected.id);
    if (startIndex === -1) {
      return;
    }
    this.playerService.startPlaylist(PLAYLIST_ID, playable, startIndex);
  }

  pauseTune(): void {
    this.playerService.pause();
  }

  private createSearchState(): Observable<SearchState> {
    return this.requestStore.pipe(
      filter((request): request is MusicSearchRequest => request !== null),
      switchMap(request => {
        const start = (request.page - 1) * ELEMENTS_ON_PAGE;
        return this.api.search(request.filters, start, ELEMENTS_ON_PAGE).pipe(
          map(response => this.toSearchState(response, request.page)),
          startWith({
            ...EMPTY_SEARCH_STATE,
            currentPage: request.page,
            loading: true,
          }),
        );
      }),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private toSearchState(response: MusicSearchResponse | null, currentPage: number): SearchState {
    if (response === null) {
      return {
        ...EMPTY_SEARCH_STATE,
        loading: false,
        error: true,
        currentPage,
      };
    }

    return {
      loading: false,
      error: false,
      totalAmount: response.totalAmount,
      currentPage,
      pagesAmount: Math.ceil(response.totalAmount / ELEMENTS_ON_PAGE),
      resultsType: response.resultsType,
      tunes: response.tunes,
      authors: response.authors,
      formats: response.formats,
      apiUrl: response.apiUrl,
      zipUrl: response.zipUrl,
    };
  }

  private createRestoredLocations(): Observable<{countries: TagItem[]; cities: TagItem[]}> {
    return this.restoredLocationIdsStore.pipe(
      switchMap(({countryIds, cityIds}) => {
        const ids = [...countryIds, ...cityIds];
        if (ids.length === 0) {
          return of({countries: [] as TagItem[], cities: [] as TagItem[]});
        }
        return this.api.resolveLocations(ids).pipe(
          map(locations => ({
            countries: this.pickLocations(locations, countryIds),
            cities: this.pickLocations(locations, cityIds),
          })),
        );
      }),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private createLocationItems(
    restoredItems$: Observable<TagItem[]>,
    manualItemsStore: BehaviorSubject<TagItem[]>,
    removedIdsStore: BehaviorSubject<number[]>,
  ): Observable<TagItem[]> {
    return combineLatest([restoredItems$, manualItemsStore, removedIdsStore]).pipe(
      map(([restoredItems, manualItems, removedIds]) => {
        const availableRestoredItems = restoredItems.filter(item => item.id !== null && !removedIds.includes(item.id));
        return [...availableRestoredItems, ...manualItems]
          .filter((item, index, items) => item.id === null || items.findIndex(other => other.id === item.id) === index);
      }),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private createMobileState(): Observable<boolean> {
    return this.breakpointObserver.observe(ZxBreakpoints.MobileAll).pipe(
      map(state => state.matches),
      startWith(false),
      distinctUntilChanged(),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private createOptionsState(searchState$: Observable<SearchState>): Observable<SearchOptionsState> {
    const formatGroupKeys = MUSIC_SEARCH_FORMAT_GROUPS.map(group => `player.formatGroup.${group}`);
    const sortKeys = MUSIC_SEARCH_SORT_PARAMETERS.map(parameter => `music-search.sort.${parameter}`);
    const staticKeys = ['music-search.all-formats', 'music-search.all-format-groups', 'music-search.any-rating'];

    return combineLatest([
      this.translateService.stream([...formatGroupKeys, ...sortKeys, ...staticKeys]),
      searchState$.pipe(map(state => state.formats), distinctUntilChanged()),
    ]).pipe(
      map(([translations, formats]) => ({
        formatOptions: [
          {value: '', label: translations['music-search.all-formats'] ?? ''},
          ...formats.map(format => ({value: format, label: format})),
        ],
        formatGroupOptions: [
          {value: '', label: translations['music-search.all-format-groups'] ?? ''},
          ...MUSIC_SEARCH_FORMAT_GROUPS.map(group => ({
            value: group,
            label: translations[`player.formatGroup.${group}`] ?? group,
          })),
        ],
        ratingOptions: [
          {value: '', label: translations['music-search.any-rating'] ?? ''},
          {value: '1', label: '★ 1+'},
          {value: '2', label: '★ 2+'},
          {value: '3', label: '★ 3+'},
          {value: '4', label: '★ 4+'},
          {value: '5', label: '★ 5'},
        ],
        sortOptions: MUSIC_SEARCH_SORT_PARAMETERS.map(parameter => ({
          value: parameter,
          label: translations[`music-search.sort.${parameter}`] ?? parameter,
        })),
      })),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private createTagsSearchState(
    query$: Subject<string>,
    search: (query: string) => Observable<TagItem[]>,
  ): Observable<TagsSearchState> {
    return query$.pipe(
      debounceTime(SEARCH_DEBOUNCE_MS),
      distinctUntilChanged(),
      switchMap(query => {
        const normalized = query.trim();
        if (normalized === '') {
          return of(EMPTY_TAGS_SEARCH_STATE);
        }
        return search(normalized).pipe(
          map(results => ({results, loading: false})),
          catchError(() => of(EMPTY_TAGS_SEARCH_STATE)),
          startWith({results: [] as TagItem[], loading: true}),
        );
      }),
      startWith(EMPTY_TAGS_SEARCH_STATE),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private getRangeStart(state: SearchState): number {
    if (state.totalAmount === 0) {
      return 0;
    }
    return (state.currentPage - 1) * ELEMENTS_ON_PAGE + 1;
  }

  private getRangeEnd(state: SearchState): number {
    return Math.min(state.currentPage * ELEMENTS_ON_PAGE, state.totalAmount);
  }

  private updateUrl(): void {
    const newPath = buildMusicSearchPath(this.urlBase, this.appliedFilters, this.currentPage);
    window.history.pushState(null, '', newPath);
  }

  private pickLocations(locations: MusicSearchLocation[], ids: number[]): TagItem[] {
    return locations
      .filter(location => ids.includes(location.id))
      .map(location => ({id: location.id, title: location.title, description: null}));
  }

  private locationsToTags(locations: MusicSearchLocation[]): TagItem[] {
    return locations.map(location => ({
      id: location.id,
      title: location.title,
      description: null,
    }));
  }

  private loadIcons(): void {
    for (const icon of ['settings', 'expand-more', 'expand-less', 'open-in-new', 'download', 'search']) {
      this.iconRegistry.loadSvg(`${environment.svgUrl}${icon}.svg`, icon)?.subscribe();
    }
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
}
