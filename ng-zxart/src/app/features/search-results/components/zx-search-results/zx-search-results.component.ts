import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {merge, Subject, Subscription} from 'rxjs';
import {debounceTime, filter, map, switchMap, tap} from 'rxjs/operators';
import {SearchResultsService} from '../../services/search-results.service';
import {PressArticleDto, SearchItemDto, SearchResultsDto, SearchResultSetDto} from '../../models/search-item.dto';
import {AuthorListItem} from '../../../author-browser/models/author-list-item';
import {GroupListItem} from '../../../group-browser/models/group-list-item';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxProdDto} from '../../../../shared/models/zx-prod-dto';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {PartyDto} from '../../../../shared/models/party-dto';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {
  ZxBodyDirective,
  ZxCaptionDirective,
  ZxHeading1Directive,
  ZxHeading2Directive,
} from '../../../../shared/directives/typography/typography.directives';
import {ZxCheckboxFieldComponent} from '../../../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxArticlePreviewComponent} from '../../../../shared/ui/zx-article-preview/zx-article-preview.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxAuthorsTableComponent} from '../../../../shared/ui/zx-authors-table/zx-authors-table.component';
import {ZxGroupsTableComponent} from '../../../../shared/ui/zx-groups-table/zx-groups-table.component';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {
  ZxPictureCardSkeletonComponent
} from '../../../../shared/ui/zx-picture-card-skeleton/zx-picture-card-skeleton.component';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {ZxProdBlockComponent} from '../../../../shared/ui/zx-prod-block/zx-prod-block.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxPartyCardComponent} from '../../../../shared/ui/zx-party-card/zx-party-card.component';
import {PlayerService} from '../../../player/services/player.service';

const AUTHOR_SET_TYPE = 'author';
const GROUP_SET_TYPE = 'group';
const PICTURE_SET_TYPE = 'zxPicture';
const MUSIC_SET_TYPE = 'zxMusic';
const PRESS_ARTICLE_SET_TYPE = 'pressArticle';
const PARTY_SET_TYPE = 'party';
const PROD_SET_TYPES = new Set(['zxProd', 'zxRelease']);

interface TypeFilterOption {
  readonly value: string;
  readonly label: string;
  selected: boolean;
}

interface SkeletonGroup {
  readonly variant: 'row' | 'picture-grid' | 'prod-grid';
  readonly count: number;
}

const INITIAL_SKELETON_GROUPS: SkeletonGroup[] = [
  {variant: 'row', count: 4},
  {variant: 'prod-grid', count: 6},
  {variant: 'picture-grid', count: 6},
  {variant: 'row', count: 3},
];

@Component({
  selector: 'zx-search-results',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxPaginationComponent,
    ZxSkeletonComponent,
    ZxArticlePreviewComponent,
    ZxCheckboxFieldComponent,
    ZxFilterBarComponent,
    ZxButtonComponent,
    ZxSpinnerComponent,
    ZxAuthorsTableComponent,
    ZxGroupsTableComponent,
    ZxPictureCardComponent,
    ZxPictureCardSkeletonComponent,
    ZxPicturesGridDirective,
    ZxProdBlockComponent,
    ZxPanelComponent,
    ZxTableComponent,
    ZxTuneRowComponent,
    ZxPartyCardComponent,
    ZxBodyDirective,
    ZxCaptionDirective,
    ZxHeading1Directive,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-search-results.component.html',
  styleUrls: ['./zx-search-results.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSearchResultsComponent implements OnInit, OnDestroy {
  @Input() elementId = '';
  @Input() baseUrl = '';

  readonly skeletonGroups = INITIAL_SKELETON_GROUPS;
  readonly pictureSkeletonItems = [0, 1, 2, 3, 4, 5];

  initialLoading = true;
  pageLoading = false;
  error = false;
  data: SearchResultsDto | null = null;

  phrase = '';
  currentPage = 1;
  pagesAmount = 0;
  urlBase = '';

  typeFilters: TypeFilterOption[] = [];

  readonly playingTuneId$ = this.playerService.state$.pipe(
    map(state => state.isPlaying && state.playlistId === this.musicPlaylistId()
      ? (state.playlist[state.currentIndex]?.id ?? null)
      : null),
  );

  private prodsBySet: Record<string, ZxProd[]> = {};

  private readonly trigger = new Subject<{kind: 'initial' | 'page' | 'filter'}>();
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly searchService: SearchResultsService,
    private readonly translateService: TranslateService,
    private readonly playerService: PlayerService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    const parsed = this.parseUrl();
    this.phrase = parsed.phrase;
    this.currentPage = parsed.page;
    this.urlBase = this.computeUrlBase();

    const urlTypes = this.parseTypesParam();
    if (urlTypes.length > 0) {
      this.typeFilters = urlTypes.map(value => ({value, label: value, selected: true}));
    }

    const filterStream$ = this.trigger.pipe(filter(t => t.kind === 'filter'), debounceTime(500));
    const immediateStream$ = this.trigger.pipe(filter(t => t.kind !== 'filter'));
    this.subscriptions.add(
      merge(immediateStream$, filterStream$).pipe(
        tap(t => this.beginLoad(t.kind === 'initial')),
        switchMap(() => this.searchService.query(this.phrase, this.currentPage, this.selectedTypes())),
      ).subscribe(response => {
        if (response === null) {
          this.failLoad();
        } else {
          this.applyResponse(response);
        }
      }),
    );

    this.trigger.next({kind: 'initial'});
  }

  ngOnDestroy(): void {
    this.trigger.complete();
    this.subscriptions.unsubscribe();
  }

  trackBySetType = (_: number, set: SearchResultSetDto): string => set.type;
  trackByItemId = (_: number, item: SearchItemDto): number => item.id;
  trackByProdId = (_: number, prod: ZxProd): number => prod.id;
  trackByTuneId = (_: number, tune: ZxTuneDto): number => tune.id;
  trackByPartyId = (_: number, party: PartyDto): number => party.id;
  trackByOptionValue = (_: number, opt: TypeFilterOption): string => opt.value;

  onPageChange(page: number): void {
    if (page === this.currentPage) {
      return;
    }
    this.currentPage = page;
    this.updateUrl();
    this.trigger.next({kind: 'page'});
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  onTypeToggle(option: TypeFilterOption, checked: boolean): void {
    option.selected = checked;
    this.currentPage = 1;
    this.updateUrl();
    this.trigger.next({kind: 'filter'});
  }

  onSelectAllTypes(): void {
    const allSelected = this.typeFilters.every(opt => opt.selected);
    if (allSelected) {
      return;
    }
    this.typeFilters = this.typeFilters.map(opt => ({...opt, selected: true}));
    this.currentPage = 1;
    this.updateUrl();
    this.trigger.next({kind: 'filter'});
  }

  isAuthorSet(set: SearchResultSetDto): boolean {
    return set.type === AUTHOR_SET_TYPE;
  }

  isGroupSet(set: SearchResultSetDto): boolean {
    return set.type === GROUP_SET_TYPE;
  }

  isPictureSet(set: SearchResultSetDto): boolean {
    return set.type === PICTURE_SET_TYPE;
  }

  isMusicSet(set: SearchResultSetDto): boolean {
    return set.type === MUSIC_SET_TYPE;
  }

  isProdSet(set: SearchResultSetDto): boolean {
    return PROD_SET_TYPES.has(set.type);
  }

  isPressArticleSet(set: SearchResultSetDto): boolean {
    return set.type === PRESS_ARTICLE_SET_TYPE;
  }

  isPartySet(set: SearchResultSetDto): boolean {
    return set.type === PARTY_SET_TYPE;
  }

  asAuthors(items: SearchItemDto[]): AuthorListItem[] {
    return items as AuthorListItem[];
  }

  asGroups(items: SearchItemDto[]): GroupListItem[] {
    return items as GroupListItem[];
  }

  asPictures(items: SearchItemDto[]): ZxPictureDto[] {
    return items as ZxPictureDto[];
  }

  asTunes(items: SearchItemDto[]): ZxTuneDto[] {
    return items as unknown as ZxTuneDto[];
  }

  asPressArticles(items: SearchItemDto[]): PressArticleDto[] {
    return items as PressArticleDto[];
  }

  asParties(items: SearchItemDto[]): PartyDto[] {
    return items as PartyDto[];
  }

  prodsFor(set: SearchResultSetDto): ZxProd[] {
    return this.prodsBySet[set.type] ?? [];
  }

  setLabelKey(type: string): string {
    return 'search.types.' + type;
  }

  headingTranslationKey(): string {
    return this.currentPage > 1 ? 'search.heading.with_page' : 'search.heading.first_page';
  }

  pictureGalleryId(set: SearchResultSetDto): string {
    return 'search-' + set.type;
  }

  musicPlaylistId(): string {
    return 'search-music-' + this.elementId;
  }

  playTune(tunes: ZxTuneDto[], tune: ZxTuneDto): void {
    const playable = tunes.filter(t => t.isPlayable && t.mp3Url);
    const startIndex = playable.findIndex(t => t.id === tune.id);
    if (startIndex === -1) {
      return;
    }
    this.playerService.startPlaylist(this.musicPlaylistId(), playable, startIndex);
  }

  pauseTune(): void {
    this.playerService.pause();
  }

  private beginLoad(initial: boolean): void {
    if (initial) {
      this.initialLoading = true;
    } else {
      this.pageLoading = true;
    }
    this.error = false;
    this.cdr.markForCheck();
  }

  private applyResponse(response: SearchResultsDto): void {
    this.data = response;
    this.prodsBySet = this.buildProdInstances(response);
    this.pagesAmount = response.pageSize > 0 ? Math.ceil(response.total / response.pageSize) : 0;
    this.syncTypeFilters(response.sets.map(s => s.type));
    this.initialLoading = false;
    this.pageLoading = false;
    this.cdr.markForCheck();
  }

  private failLoad(): void {
    this.initialLoading = false;
    this.pageLoading = false;
    this.error = true;
    this.cdr.markForCheck();
  }

  private buildProdInstances(response: SearchResultsDto): Record<string, ZxProd[]> {
    const map: Record<string, ZxProd[]> = {};
    for (const set of response.sets) {
      if (!PROD_SET_TYPES.has(set.type)) {
        continue;
      }
      map[set.type] = (set.items as ZxProdDto[]).map(dto => new ZxProd(dto));
    }
    return map;
  }

  private syncTypeFilters(discoveredTypes: string[]): void {
    const previousSelections = new Map(this.typeFilters.map(opt => [opt.value, opt.selected]));
    const hadPriorSelections = previousSelections.size > 0;
    const merged = [...this.typeFilters.map(opt => opt.value)];
    for (const type of discoveredTypes) {
      if (!merged.includes(type)) {
        merged.push(type);
      }
    }
    if (merged.length === this.typeFilters.length
      && merged.every((t, i) => this.typeFilters[i]?.value === t)) {
      return;
    }
    this.typeFilters = merged.map(value => {
      const selected = hadPriorSelections ? (previousSelections.get(value) ?? true) : true;
      return {value, label: value, selected};
    });
    this.translateLabels();
  }

  private translateLabels(): void {
    if (this.typeFilters.length === 0) {
      return;
    }
    const keys = this.typeFilters.map(opt => this.setLabelKey(opt.value));
    this.subscriptions.add(
      this.translateService.stream(keys).subscribe(translations => {
        this.typeFilters = this.typeFilters.map(opt => ({
          ...opt,
          label: translations[this.setLabelKey(opt.value)] ?? opt.value,
        }));
        this.cdr.markForCheck();
      }),
    );
  }

  private selectedTypes(): string[] {
    if (this.typeFilters.length === 0) {
      return [];
    }
    const selected = this.typeFilters.filter(opt => opt.selected).map(opt => opt.value);
    if (selected.length === this.typeFilters.length) {
      return [];
    }
    return selected;
  }

  private parseUrl(): {phrase: string; page: number} {
    const path = window.location.pathname;
    const phraseMatch = path.match(/\/phrase:([^/]*)/);
    const pageMatch = path.match(/\/page:(\d+)/);
    return {
      phrase: phraseMatch ? this.decodeUrlPhrase(decodeURIComponent(phraseMatch[1])) : '',
      page: pageMatch ? Math.max(1, parseInt(pageMatch[1], 10)) : 1,
    };
  }

  private decodeUrlPhrase(value: string): string {
    return value.replace(/%s%/g, '/');
  }

  private encodeUrlPhrase(value: string): string {
    return encodeURIComponent(value.replace(/\//g, '%s%'));
  }

  private parseTypesParam(): string[] {
    const params = new URLSearchParams(window.location.search);
    const raw = params.get('types');
    if (!raw) {
      return [];
    }
    return raw.split(',').map(p => p.trim()).filter(p => p.length > 0);
  }

  private computeUrlBase(): string {
    if (this.baseUrl && this.elementId && this.phrase !== '') {
      const base = this.baseUrl.endsWith('/') ? this.baseUrl : this.baseUrl + '/';
      return base + 'action:perform/id:' + this.elementId + '/phrase:' + this.encodeUrlPhrase(this.phrase) + '/';
    }
    const cleanPath = window.location.pathname.replace(/\/page:\d+\/?/, '');
    return cleanPath.endsWith('/') ? cleanPath : cleanPath + '/';
  }

  private updateUrl(): void {
    const pagePath = this.currentPage > 1
      ? this.urlBase + 'page:' + this.currentPage + '/'
      : this.urlBase;

    const params = new URLSearchParams(window.location.search);
    const selected = this.typeFilters.filter(opt => opt.selected).map(opt => opt.value);
    const allSelected = this.typeFilters.length > 0 && selected.length === this.typeFilters.length;
    if (allSelected || selected.length === 0) {
      params.delete('types');
    } else {
      params.set('types', selected.join(','));
    }
    const queryString = params.toString();
    const newUrl = queryString ? pagePath + '?' + queryString : pagePath;
    window.history.pushState(null, '', newUrl);
  }
}
