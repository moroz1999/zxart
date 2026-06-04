import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {forkJoin, Observable, Subscription} from 'rxjs';
import {map} from 'rxjs/operators';
import {BreakpointObserver} from '@angular/cdk/layout';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxRowSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ProdReleasesApiService} from '../../services/prod-releases-api.service';
import {ProdReleaseDto} from '../../models/prod-release.dto';
import {ZxProdReleaseRowComponent} from '../zx-prod-release-row/zx-prod-release-row.component';
import {ElementPrivilegesApiService} from '../../../../shared/services/element-privileges-api.service';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxProdReleaseCardComponent} from '../../../../entities/zx-prod-release-card/zx-prod-release-card.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxToggleComponent, ZxToggleOption} from '../../../../shared/ui/zx-toggle/zx-toggle.component';
import {ZxBreakpoints} from '../../../../shared/breakpoints';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';

const DISK_FORMATS = new Set(['dsk', 'trd', 'scl', 'fdi', 'udi', 'td0', 'mgt', 'opd', 'mld', 'mbd', 'img', 'sad', 'd40', 'd80', 'cpm']);
const TAPE_FORMATS = new Set(['tzx', 'tap', 'mdr', 'p', 'o']);

interface LabeledOption {
  code: string;
  label: string;
}

type SortField = 'title' | 'year' | 'downloads' | 'plays' | 'rating';
type ViewMode = 'table' | 'cards';
type MediaFilter = 'all' | 'disk' | 'tape' | 'other';

@Component({
  selector: 'zx-prod-releases-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxRowSkeletonComponent,
    ZxTableComponent,
    ZxProdReleaseRowComponent,
    ZxFilterBarComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxProdReleaseCardComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxPanelComponent,
    ZxToggleComponent,
  ],
  templateUrl: './zx-prod-releases-section.component.html',
  styleUrls: ['./zx-prod-releases-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleasesSectionComponent implements OnInit, OnDestroy {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) prodUrl!: string;

  readonly viewToggleOptions$: Observable<ZxToggleOption[]>;

  loading = false;
  loaded = false;
  releases: ProdReleaseDto[] = [];
  canUploadScreenshot = false;

  filterLang = 'all';
  filterType = 'all';
  filterMedia: MediaFilter = 'all';
  activeSortField: SortField | null = null;
  sortDir: 'asc' | 'desc' = 'desc';
  viewMode: ViewMode = 'table';
  isMobile = false;

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.releases.length === 0 ? 'none' : '';
  }

  private readonly subscription = new Subscription();

  constructor(
    private readonly api: ProdReleasesApiService,
    private readonly elementPrivilegesApi: ElementPrivilegesApiService,
    private readonly cdr: ChangeDetectorRef,
    private readonly iconReg: SvgIconRegistryService,
    breakpointObserver: BreakpointObserver,
    translateService: TranslateService,
  ) {
    this.viewToggleOptions$ = translateService.stream([
      'release-row.view-table',
      'release-row.view-cards',
    ]).pipe(
      map(t => [
        {value: 'table', icon: 'list', label: t['release-row.view-table'] as string},
        {value: 'cards', icon: 'grid', label: t['release-row.view-cards'] as string},
      ])
    );

    this.subscription.add(
      breakpointObserver.observe(ZxBreakpoints.MobileAll).subscribe(state => {
        this.isMobile = state.matches;
        this.cdr.markForCheck();
      })
    );
  }

  ngOnInit(): void {
    for (const name of ['list', 'grid']) {
      this.iconReg.loadSvg(`${environment.svgUrl}${name}.svg`, name)?.subscribe();
    }
  }

  get availableLangs(): LabeledOption[] {
    const seen = new Map<string, string>();
    for (const r of this.releases) {
      for (const l of r.languages) {
        if (!seen.has(l.code)) {
          seen.set(l.code, `${l.emoji} ${l.code.toUpperCase()}`);
        }
      }
    }
    return Array.from(seen.entries())
      .filter(([code]) => this.releases.some(r => !r.languages.some(l => l.code === code)))
      .map(([code, label]) => ({code, label}));
  }

  get availableTypes(): LabeledOption[] {
    const seen = new Map<string, string>();
    for (const r of this.releasesForTypeOptions) {
      if (r.releaseType && r.releaseTypeLabel && !seen.has(r.releaseType)) {
        seen.set(r.releaseType, r.releaseTypeLabel);
      }
    }
    return Array.from(seen.entries()).map(([code, label]) => ({code, label}));
  }

  get availableMediaTypes(): MediaFilter[] {
    const found = new Set<MediaFilter>();
    for (const r of this.releases) {
      for (const f of r.formats) {
        found.add(this.getMediaCategory(f.format));
      }
    }
    return Array.from(found);
  }

  get allLangOptions(): LabeledOption[] {
    return [{code: 'all', label: 'All'}, ...this.availableLangs];
  }

  get allTypeOptions(): LabeledOption[] {
    return [{code: 'all', label: 'All'}, ...this.availableTypes];
  }

  get filteredReleases(): ProdReleaseDto[] {
    let result = this.releases;

    if (this.filterLang !== 'all') {
      result = result.filter(r => r.languages.some(l => l.code === this.filterLang));
    }

    if (this.filterType !== 'all') {
      result = result.filter(r => r.releaseType === this.filterType);
    }

    if (this.filterMedia !== 'all') {
      const media = this.filterMedia;
      result = result.filter(r => r.formats.some(f => this.getMediaCategory(f.format) === media));
    }

    if (this.activeSortField) {
      const field = this.activeSortField;
      const dir = this.sortDir;
      result = [...result].sort((a, b) => {
        const compareResult = this.compareReleases(a, b, field);
        return dir === 'desc' ? -compareResult : compareResult;
      });
    }

    return result;
  }

  private getMediaCategory(format: string): MediaFilter {
    const fmt = format.toLowerCase();
    if (DISK_FORMATS.has(fmt)) return 'disk';
    if (TAPE_FORMATS.has(fmt)) return 'tape';
    return 'other';
  }

  private compareReleases(a: ProdReleaseDto, b: ProdReleaseDto, field: SortField): number {
    if (field === 'title') {
      return a.title.localeCompare(b.title);
    }

    const va = this.getNumericSortValue(a, field);
    const vb = this.getNumericSortValue(b, field);
    return va - vb;
  }

  private getNumericSortValue(r: ProdReleaseDto, field: Exclude<SortField, 'title'>): number {
    switch (field) {
      case 'year': return r.year || 0;
      case 'downloads': return r.downloadsCount || 0;
      case 'plays': return r.playsCount || 0;
      case 'rating': return r.voting.votes || 0;
    }
  }

  setFilterLang(code: string): void {
    this.filterLang = code;
    if (this.filterType !== 'all' && !this.availableTypes.some(type => type.code === this.filterType)) {
      this.filterType = 'all';
    }
    this.cdr.detectChanges();
  }

  setFilterType(code: string): void {
    this.filterType = code;
    this.cdr.detectChanges();
  }

  setFilterMedia(value: string): void {
    this.filterMedia = value as MediaFilter;
    this.cdr.detectChanges();
  }

  setViewMode(mode: ViewMode): void {
    this.viewMode = mode;
    this.cdr.detectChanges();
  }

  setViewModeFromToggle(value: string): void {
    if (value === 'table' || value === 'cards') {
      this.setViewMode(value);
    }
  }

  sortBy(field: SortField): void {
    if (this.activeSortField === field) {
      this.sortDir = this.sortDir === 'desc' ? 'asc' : 'desc';
    } else {
      this.activeSortField = field;
      this.sortDir = field === 'title' ? 'asc' : 'desc';
    }
    this.cdr.detectChanges();
  }

  sortArrow(field: SortField): string {
    if (this.activeSortField !== field) return '';
    return this.sortDir === 'desc' ? ' ↓' : ' ↑';
  }

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.subscription.add(
      forkJoin({
        releases: this.api.getReleases(this.elementId),
        privileges: this.elementPrivilegesApi.getPrivileges(this.elementId, ['uploadScreenshot']),
      }).subscribe(({releases, privileges}) => {
        this.releases = releases;
        this.canUploadScreenshot = privileges.uploadScreenshot === true;
        this.loaded = true;
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  trackById(_index: number, release: ProdReleaseDto): number {
    return release.id;
  }

  trackByCode(_index: number, option: LabeledOption): string {
    return option.code;
  }

  get screenshotUploadUrl(): string {
    return `${this.prodUrl}id:${this.elementId}/action:uploadScreenshot/`;
  }

  private get releasesForTypeOptions(): ProdReleaseDto[] {
    if (this.filterLang === 'all') {
      return this.releases;
    }
    return this.releases.filter(r => r.languages.some(l => l.code === this.filterLang));
  }
}
