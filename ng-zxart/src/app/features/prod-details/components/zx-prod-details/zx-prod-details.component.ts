import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {
  ZxProdDetailsSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-prod-details-skeleton/zx-prod-details-skeleton.component';
import {ZxYoutubeEmbedComponent} from '../../../../shared/ui/zx-youtube-embed/zx-youtube-embed.component';
import {ProdCoreApiService} from '../../services/prod-core-api.service';
import {ProdCoreDto} from '../../models/prod-core.dto';
import {ZxProdHeroComponent} from '../zx-prod-hero/zx-prod-hero.component';
import {ZxProdDescriptionComponent} from '../zx-prod-description/zx-prod-description.component';
import {ZxProdInstructionsComponent} from '../zx-prod-instructions/zx-prod-instructions.component';
import {ZxProdScreenshotsSectionComponent,} from '../zx-prod-screenshots-section/zx-prod-screenshots-section.component';
import {ZxProdReleasesSectionComponent,} from '../zx-prod-releases-section/zx-prod-releases-section.component';
import {ZxProdArticlesSectionComponent,} from '../zx-prod-articles-section/zx-prod-articles-section.component';
import {ZxProdMentionsSectionComponent,} from '../zx-prod-mentions-section/zx-prod-mentions-section.component';
import {
  ZxProdCompilationItemsSectionComponent,
} from '../zx-prod-compilation-items-section/zx-prod-compilation-items-section.component';
import {
  ZxProdSeriesSectionComponent,
} from '../zx-prod-series-section/zx-prod-series-section.component';
import {
  ZxProdSeriesProdsSectionComponent,
} from '../zx-prod-series-prods-section/zx-prod-series-prods-section.component';
import {
  ZxProdCompilationsSectionComponent,
} from '../zx-prod-compilations-section/zx-prod-compilations-section.component';
import {ZxProdMusicSectionComponent,} from '../zx-prod-music-section/zx-prod-music-section.component';
import {ZxProdPicturesSectionComponent,} from '../zx-prod-pictures-section/zx-prod-pictures-section.component';
import {ZxProdInlaysSectionComponent,} from '../zx-prod-inlays-section/zx-prod-inlays-section.component';
import {ZxProdMapsSectionComponent,} from '../zx-prod-maps-section/zx-prod-maps-section.component';
import {ZxProdRzxSectionComponent,} from '../zx-prod-rzx-section/zx-prod-rzx-section.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxGridItemDirective} from '../../../../shared/ui/zx-grid/zx-grid-item.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';
import {TranslateModule} from '@ngx-translate/core';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TagsListComponent} from '../../../../shared/lib/tags-list/tags-list.component';
import {ZxProdInstructionsSectionComponent} from '../zx-prod-instructions-section/zx-prod-instructions-section.component';

type ProdMainTabId = 'releases' | 'media' | 'links' | 'discussion';
type ProdMediaTabId = 'description' | 'inlays' | 'maps' | 'rzx' | 'graphics' | 'music' | 'instructions';
type ProdLinksTabId = 'articles' | 'series' | 'compilations';

@Component({
  selector: 'zx-prod-details',
  standalone: true,
  imports: [
    CommonModule,
    ZxProdDetailsSkeletonComponent,
    ZxYoutubeEmbedComponent,
    ZxProdHeroComponent,
    ZxProdDescriptionComponent,
    ZxProdInstructionsComponent,
    ZxProdScreenshotsSectionComponent,
    ZxProdReleasesSectionComponent,
    ZxProdArticlesSectionComponent,
    ZxProdMentionsSectionComponent,
    ZxProdCompilationItemsSectionComponent,
    ZxProdSeriesSectionComponent,
    ZxProdSeriesProdsSectionComponent,
    ZxProdCompilationsSectionComponent,
    ZxProdMusicSectionComponent,
    ZxProdPicturesSectionComponent,
    ZxProdInlaysSectionComponent,
    ZxProdMapsSectionComponent,
    ZxProdRzxSectionComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxGridComponent,
    ZxGridItemDirective,
    ZxInlineComponent,
    ZxTabsComponent,
    ZxTabComponent,
    ZxTabContentDirective,
    TranslateModule,
    CommentsListComponent,
    RatingsListComponent,
    ZxBreadcrumbsComponent,
    TextDirective,
    HeadingDirective,
    TagsListComponent,
    ZxProdInstructionsSectionComponent,
  ],
  templateUrl: './zx-prod-details.component.html',
  styleUrls: ['./zx-prod-details.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDetailsComponent implements OnInit {
  @Input() elementId = 0;

  core$: Observable<ProdCoreDto | null> = of(null);

  constructor(private readonly api: ProdCoreApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      this.core$ = of(null);
      return;
    }
    this.core$ = this.api.getCore(+this.elementId).pipe(shareReplay(1));
  }

  getMainTabIndex(core: ProdCoreDto): number {
    const tabs = this.getMainTabs(core);

    const requestedTab = this.getRequestedTabId();
    const requestedTabIndex = this.getRequestedTabIndex(tabs, requestedTab);
    if (requestedTabIndex !== null) {
      return requestedTabIndex;
    }

    if (this.getRequestedTabIndex(this.getMediaTabs(core), requestedTab) !== null && tabs.includes('media')) {
      return this.getTabIndex(tabs, 'media');
    }

    if (this.getRequestedTabIndex(this.getLinksTabs(core), requestedTab) !== null && tabs.includes('links')) {
      return this.getTabIndex(tabs, 'links');
    }

    return 0;
  }

  getMediaTabIndex(core: ProdCoreDto): number {
    return this.getTabIndex(this.getMediaTabs(core), this.getRequestedTabId());
  }

  getLinksTabIndex(core: ProdCoreDto): number {
    return this.getTabIndex(this.getLinksTabs(core), this.getRequestedTabId());
  }

  getMainTabHref(tabId: ProdMainTabId): string {
    return this.getTabHref(tabId);
  }

  getMediaTabHref(tabId: ProdMediaTabId): string {
    return this.getTabHref(tabId);
  }

  getLinksTabHref(tabId: ProdLinksTabId): string {
    return this.getTabHref(tabId);
  }

  private getTabHref(tabId: string): string {
    const url = this.getCurrentUrl();
    url.pathname = this.replaceTabPath(url.pathname, tabId);
    url.searchParams.delete('tab');
    url.searchParams.delete('media');
    url.searchParams.delete('links');
    url.hash = '';

    return this.formatUrl(url);
  }

  private getMainTabs(core: ProdCoreDto): ProdMainTabId[] {
    const tabs: ProdMainTabId[] = [];

    if (core.tabs.hasReleases) {
      tabs.push('releases');
    }

    if (this.getMediaTabs(core).length) {
      tabs.push('media');
    }

    if (this.getLinksTabs(core).length) {
      tabs.push('links');
    }

    tabs.push('discussion');

    return tabs;
  }

  private getMediaTabs(core: ProdCoreDto): ProdMediaTabId[] {
    const tabs: ProdMediaTabId[] = [];

    if (core.tabs.hasDescription) tabs.push('description');
    if (core.tabs.hasInlays) tabs.push('inlays');
    if (core.tabs.hasMaps) tabs.push('maps');
    if (core.tabs.hasRzx) tabs.push('rzx');
    if (core.tabs.hasPictures) tabs.push('graphics');
    if (core.tabs.hasTunes) tabs.push('music');
    if (core.tabs.hasInstructions || core.tabs.hasTextInstructions) tabs.push('instructions');

    return tabs;
  }

  private getLinksTabs(core: ProdCoreDto): ProdLinksTabId[] {
    const tabs: ProdLinksTabId[] = [];

    if (core.tabs.hasArticles) tabs.push('articles');
    if (core.tabs.hasSeriesProds || core.tabs.isInSeries) tabs.push('series');
    if (core.tabs.hasCompilations) tabs.push('compilations');

    return tabs;
  }

  private getTabIndex<T extends string>(tabs: T[], requestedTab: string | null): number {
    const index = requestedTab ? tabs.indexOf(requestedTab as T) : -1;

    return index >= 0 ? index : 0;
  }

  private getRequestedTabIndex<T extends string>(tabs: T[], requestedTab: string | null): number | null {
    if (!requestedTab) {
      return null;
    }

    const index = tabs.indexOf(requestedTab as T);

    return index >= 0 ? index : null;
  }

  private getRequestedTabId(): string | null {
    const match = window.location.pathname.match(/\/tabs:([^/]+)(?=\/|$)/);

    return match ? decodeURIComponent(match[1]) : null;
  }

  private getCurrentUrl(): URL {
    return new URL(window.location.href);
  }

  private replaceTabPath(path: string, tabId: string): string {
    const cleanPath = path.replace(/\/tabs:[^/]+(?=\/|$)/, '');
    const normalizedPath = cleanPath.endsWith('/') ? cleanPath : `${cleanPath}/`;

    return `${normalizedPath}tabs:${encodeURIComponent(tabId)}/`;
  }

  private formatUrl(url: URL): string {
    return `${url.pathname}${url.search}${url.hash}`;
  }
}
