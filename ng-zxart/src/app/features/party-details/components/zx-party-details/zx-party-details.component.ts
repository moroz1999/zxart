import {ChangeDetectionStrategy, Component, ElementRef, Input, OnInit, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {PartyCoreDto} from '../../models/party-core.dto';
import {PartyCoreApiService} from '../../services/party-core-api.service';
import {ZxPartyHeaderComponent} from '../zx-party-header/zx-party-header.component';
import {ZxPartyOverviewComponent} from '../zx-party-overview/zx-party-overview.component';
import {ZxPartyCompoComponent} from '../zx-party-compo/zx-party-compo.component';
import {ZxPartyRatingsComponent} from '../zx-party-ratings/zx-party-ratings.component';
import {ZxPartyCommentsComponent} from '../zx-party-comments/zx-party-comments.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxGridItemDirective} from '../../../../shared/ui/zx-grid/zx-grid-item.directive';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';

/** Static tab ids; competition tabs use their raw `compoType` as the id. */
type PartyTabId = 'overview' | 'activity' | string;

@Component({
  selector: 'zx-party-details-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxBreadcrumbsComponent,
    ZxPartyHeaderComponent,
    ZxPartyOverviewComponent,
    ZxPartyCompoComponent,
    ZxPartyRatingsComponent,
    ZxPartyCommentsComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxGridComponent,
    ZxGridItemDirective,
    ZxPanelComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxSkeletonBoneComponent,
    ZxProdsListSkeletonComponent,
    ZxTabsComponent,
    ZxTabComponent,
    ZxTabContentDirective,
    CommentsListComponent,
  ],
  templateUrl: './zx-party-details.component.html',
  styleUrl: './zx-party-details.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyDetailsComponent implements OnInit {
  @Input() elementId = 0;
  @ViewChild(ZxTabsComponent, {read: ElementRef}) private tabsRef!: ElementRef<HTMLElement>;

  readonly skeletonTabs = [0, 1, 2, 3];

  core$: Observable<PartyCoreDto | null> = of(null);

  constructor(private readonly api: PartyCoreApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      return;
    }
    this.core$ = this.api.getCore(+this.elementId).pipe(shareReplay(1));
  }

  getInitialTabIndex(core: PartyCoreDto): number {
    const requestedTab = this.getRequestedTabId();
    const index = requestedTab ? this.getTabs(core).indexOf(requestedTab) : -1;

    return index >= 0 ? index : 0;
  }

  getTabHref(tabId: PartyTabId): string {
    const path = window.location.pathname.replace(/\/tab:[^/]+(?=\/|$)/, '').replace(/\/page:\d+(?=\/|$)/, '');
    const normalizedPath = path.endsWith('/') ? path : `${path}/`;

    return `${normalizedPath}tab:${encodeURIComponent(tabId)}/`;
  }

  onTabChange(_: number): void {
    scrollToElementIfHidden(this.tabsRef?.nativeElement);
  }

  private getTabs(core: PartyCoreDto): PartyTabId[] {
    const tabs: PartyTabId[] = [];

    if (core.tabs.hasOverview) tabs.push('overview');
    for (const compo of core.compos) tabs.push(compo.compoType);
    if (core.tabs.hasActivity) tabs.push('activity');

    return tabs;
  }

  private getRequestedTabId(): string | null {
    const match = window.location.pathname.match(/\/tab:([^/]+)(?=\/|$)/);

    return match ? decodeURIComponent(match[1]) : null;
  }
}
