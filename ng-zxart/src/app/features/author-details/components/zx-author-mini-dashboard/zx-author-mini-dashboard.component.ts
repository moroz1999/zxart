import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnChanges, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {Observable, Subscription} from 'rxjs';
import {AuthorTabsDto} from '../../models/author-core.dto';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {AuthorMiniDashboardData, AuthorMiniDashboardService} from '../../services/author-mini-dashboard.service';
import {PlayerService} from '../../../player/services/player.service';
import {AuthorProdItem} from '../../services/author-prods-api.service';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxProdBlockComponent} from '../../../../shared/ui/zx-prod-block/zx-prod-block.component';
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
import {ZxPictureGridSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {ZxTuneTableSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';


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
    ZxPictureGridSkeletonComponent,
    ZxTuneTableSkeletonComponent,
    ZxProdsListSkeletonComponent,
    InViewportDirective,
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
  playingTuneId: number | null = null;

  private playlistId = '';
  private dashboardTunes: ZxTuneDto[] = [];
  private readonly subscriptions = new Subscription();

  gfxHref = '';
  musicHref = '';
  softwareHref = '';
  twoSectionLayout = false;
  picturesColumns: '1' | '2' = '1';
  requested = false;

  constructor(
    private readonly dashboardService: AuthorMiniDashboardService,
    private readonly playerService: PlayerService,
    private readonly iconReg: SvgIconRegistryService,
    private readonly cdr: ChangeDetectorRef,
  ) {
    this.data$ = this.dashboardService.data$;
  }

  ngOnInit(): void {
    this.playlistId = `author-dashboard-${this.elementId}`;
    this.iconReg.loadSvg(`${environment.svgUrl}image.svg`, 'image')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}music-note.svg`, 'music-note')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}gamepad.svg`, 'gamepad')?.subscribe();
    this.subscriptions.add(
      this.data$.subscribe(data => {
        this.dashboardTunes = data.tunes;
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

    const baseUrl = this.parseBaseUrl();
    this.gfxHref = baseUrl + 'tab:gfx/';
    this.musicHref = baseUrl + 'tab:music/';
    this.softwareHref = baseUrl + 'tab:software/';

    const sectionCount = Number(this.tabs.hasPictures) + Number(this.tabs.hasTunes) + Number(this.tabs.hasProds);
    this.twoSectionLayout = sectionCount === 2;
    this.picturesColumns = this.twoSectionLayout && this.tabs.hasPictures ? '2' : '1';

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

  toProdModel(dto: AuthorProdItem): ZxProd {
    return new ZxProd(dto);
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

  private parseBaseUrl(): string {
    let path = window.location.pathname;
    path = path.replace(/\/tab:[^/]+/g, '');
    path = path.replace(/\/page:\d+/g, '');
    return path.endsWith('/') ? path : path + '/';
  }
}
