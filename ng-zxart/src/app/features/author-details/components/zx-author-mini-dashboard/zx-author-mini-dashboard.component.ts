import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnChanges, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {map, Observable, shareReplay, Subscription} from 'rxjs';
import {AuthorTabsDto} from '../../models/author-core.dto';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {AuthorMiniDashboardData, AuthorMiniDashboardService} from '../../services/author-mini-dashboard.service';
import {PlayerService} from '../../../player/services/player.service';
import {AuthorProdEntry, AuthorReleaseEntry} from '../../services/author-prods-api.service';
import {ZxPictureCardComponent} from '../../../../entities/zx-picture-card/zx-picture-card.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxProdBlockComponent} from '../../../../entities/zx-prod-block/zx-prod-block.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxInsetComponent} from '../../../../shared/ui/zx-inset/zx-inset.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {environment} from '../../../../../environments/environment';
import {ZxProdsGridDirective} from '../../../../shared/directives/prods-grid.directive';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {ZxPictureGridSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {ZxTuneTableSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {ZxProdReleaseCardComponent} from '../../../../entities/zx-prod-release-card/zx-prod-release-card.component';

/** One dashboard work ready to render: a release card, or a prod card model. */
interface DashboardEntry {
  key: number;
  release: AuthorReleaseEntry | null;
  prod: ZxProd | null;
}

@Component({
  selector: 'zx-author-mini-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxTuneRowComponent,
    ZxProdBlockComponent,
    ZxPanelComponent,
    ZxTableComponent,
    ZxInlineComponent,
    ZxStackComponent,
    ZxGridComponent,
    ZxInsetComponent,
    ZxButtonComponent,
    SvgIconComponent,
    TextDirective,
    ZxProdsGridDirective,
    ZxPicturesGridDirective,
    ZxPictureGridSkeletonComponent,
    ZxTuneTableSkeletonComponent,
    ZxProdsListSkeletonComponent,
    InViewportDirective,
    PictureGalleryHostComponent,
    ZxProdReleaseCardComponent,
  ],
  templateUrl: './zx-author-mini-dashboard.component.html',
  styleUrl: './zx-author-mini-dashboard.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  providers: [AuthorMiniDashboardService],
})
export class ZxAuthorMiniDashboardComponent implements OnInit, OnChanges, OnDestroy {
  @Input() elementId = 0;
  @Input() tabs!: AuthorTabsDto;

  readonly data$: Observable<AuthorMiniDashboardData>;
  /**
   * The same data with each work's card model built once. Building it in the
   * template hands the card a new model on every check, and a screenshot gallery
   * told its urls changed goes back to the first shot — on the very check the
   * hover triggers.
   */
  readonly view$: Observable<AuthorMiniDashboardData & {entries: DashboardEntry[]}>;
  playingTuneId: number | null = null;

  private playlistId = '';
  private dashboardTunes: ZxTuneDto[] = [];
  private readonly subscriptions = new Subscription();

  gfxHref = '';
  musicHref = '';
  softwareHref = '';
  twoSectionLayout = false;
  expandedCardsLayout = false;
  picturesColumns: '1' | '2' = '1';
  dashboardColumns: '1' | '2' | '3' = '1';
  requested = false;

  constructor(
    private readonly dashboardService: AuthorMiniDashboardService,
    private readonly playerService: PlayerService,
    private readonly iconReg: SvgIconRegistryService,
    private readonly cdr: ChangeDetectorRef,
    private readonly pictureGalleryService: PictureGalleryService,
  ) {
    this.data$ = this.dashboardService.data$;
    this.view$ = this.data$.pipe(
      map(data => ({
        ...data,
        entries: data.prods.map((item): DashboardEntry => ({
          key: item.id,
          release: item.type === 'release' ? item as AuthorReleaseEntry : null,
          prod: item.type === 'release' ? null : new ZxProd(item as AuthorProdEntry),
        })),
      })),
      shareReplay({bufferSize: 1, refCount: true}),
    );
  }

  ngOnInit(): void {
    this.playlistId = `author-dashboard-${this.elementId}`;
    this.iconReg.loadSvg(`${environment.svgUrl}image.svg`, 'image')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}music-note.svg`, 'music-note')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}gamepad.svg`, 'gamepad')?.subscribe();
    this.subscriptions.add(
      this.data$.subscribe(data => {
        this.dashboardTunes = data.tunes;
        if (!data.loading && data.pictures.length > 0) {
          this.pictureGalleryService.ensureGalleryLoaded(`dashboard-${this.elementId}`, data.pictures);
        }
      }),
    );
    this.subscriptions.add(
      this.playerService.state$.subscribe(state => {
        const id = state.isPlaying && state.playlistId === this.playlistId
          ? (state.playlist[state.currentIndex]?.id ?? null)
          : null;
        if (id !== this.playingTuneId) {
          this.playingTuneId = id;
          this.cdr.markForCheck();
        }
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  ngOnChanges(): void {
    if (!this.tabs) {
      return;
    }

    this.gfxHref = this.tabHref('gfx');
    this.musicHref = this.tabHref('music');
    this.softwareHref = this.tabHref('software');

    const sectionCount = Number(this.tabs.hasPictures) + Number(this.tabs.hasTunes) + Number(this.tabs.hasProds);
    this.twoSectionLayout = sectionCount === 2;
    this.expandedCardsLayout = sectionCount <= 2;
    this.picturesColumns = this.twoSectionLayout && this.tabs.hasPictures ? '2' : '1';
    this.dashboardColumns = sectionCount >= 3 ? '3' : sectionCount === 2 ? '2' : '1';

    if (this.requested) {
      this.dashboardService.setContext(this.elementId, this.tabs);
    }
  }

  onInViewport(): void {
    if (this.requested) {
      return;
    }
    this.requested = true;
    this.dashboardService.setContext(this.elementId, this.tabs);
  }

  trackByEntry(_index: number, entry: DashboardEntry): number {
    return entry.key;
  }

  onPlayRequested(tune: ZxTuneDto): void {
    const playable = this.dashboardTunes.filter(t => t.isPlayable && t.mp3Url);
    const startIndex = playable.findIndex(t => t.id === tune.id);
    if (startIndex === -1) {
      return;
    }
    this.playerService.startPlaylist(this.playlistId, playable, startIndex);
  }

  onPauseRequested(): void {
    this.playerService.pause();
  }

  /** Sibling work tabs of the same author, as routed SPA URLs. */
  private tabHref(tabId: string): string {
    return `/author/${this.elementId}/${tabId}`;
  }
}
