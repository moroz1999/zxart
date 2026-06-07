import {ChangeDetectionStrategy, Component, ElementRef, Input, OnInit, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {GroupCoreDto} from '../../models/group-core.dto';
import {GroupCoreApiService} from '../../services/group-core-api.service';
import {ZxGroupHeaderComponent} from '../zx-group-header/zx-group-header.component';
import {ZxGroupBestWorksComponent} from '../zx-group-best-works/zx-group-best-works.component';
import {ZxGroupWorksComponent} from '../zx-group-works/zx-group-works.component';
import {ZxGroupRosterComponent} from '../zx-group-roster/zx-group-roster.component';
import {ZxGroupConnectionsComponent} from '../zx-group-connections/zx-group-connections.component';
import {ZxGroupMentionsComponent} from '../zx-group-mentions/zx-group-mentions.component';
import {ZxGroupRatingsComponent} from '../zx-group-ratings/zx-group-ratings.component';
import {ZxGroupCommentsComponent} from '../zx-group-comments/zx-group-comments.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxGridItemDirective} from '../../../../shared/ui/zx-grid/zx-grid-item.directive';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';

type GroupTabId = 'overview' | 'works' | 'group' | 'connections' | 'media' | 'discussion';

@Component({
  selector: 'zx-group-details-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxBreadcrumbsComponent,
    ZxGroupHeaderComponent,
    ZxGroupBestWorksComponent,
    ZxGroupWorksComponent,
    ZxGroupRosterComponent,
    ZxGroupConnectionsComponent,
    ZxGroupMentionsComponent,
    ZxGroupRatingsComponent,
    ZxGroupCommentsComponent,
    ZxGridComponent,
    ZxGridItemDirective,
    ZxStackComponent,
    ZxInlineComponent,
    ZxPanelComponent,
    ZxSkeletonBoneComponent,
    ZxTabsComponent,
    ZxTabComponent,
    ZxTabContentDirective,
    CommentsListComponent,
  ],
  templateUrl: './zx-group-details.component.html',
  styleUrl: './zx-group-details.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupDetailsComponent implements OnInit {
  @Input() elementId = 0;
  @ViewChild(ZxTabsComponent, {read: ElementRef}) private tabsRef!: ElementRef<HTMLElement>;

  core$: Observable<GroupCoreDto | null> = of(null);

  constructor(private readonly api: GroupCoreApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      return;
    }
    this.core$ = this.api.getCore(+this.elementId).pipe(shareReplay(1));
  }

  getInitialTabIndex(core: GroupCoreDto): number {
    const requestedTab = this.getRequestedTabId();
    const index = requestedTab ? this.getTabs(core).indexOf(requestedTab as GroupTabId) : -1;

    return index >= 0 ? index : 0;
  }

  getTabHref(tabId: GroupTabId): string {
    const path = window.location.pathname.replace(/\/tab:[^/]+(?=\/|$)/, '').replace(/\/page:\d+(?=\/|$)/, '');
    const normalizedPath = path.endsWith('/') ? path : `${path}/`;

    return `${normalizedPath}tab:${encodeURIComponent(tabId)}/`;
  }

  onTabChange(_: number): void {
    scrollToElementIfHidden(this.tabsRef?.nativeElement);
  }

  private getTabs(core: GroupCoreDto): GroupTabId[] {
    const tabs: GroupTabId[] = [];

    if (core.tabs.hasProds) tabs.push('overview');
    if (core.tabs.hasProds || core.tabs.hasPublished || core.tabs.hasReleases) tabs.push('works');
    if (core.tabs.hasMembers || core.tabs.hasSubgroups) tabs.push('group');
    if (core.tabs.hasConnections) tabs.push('connections');
    if (core.tabs.hasMentions) tabs.push('media');
    tabs.push('discussion');

    return tabs;
  }

  private getRequestedTabId(): string | null {
    const match = window.location.pathname.match(/\/tab:([^/]+)(?=\/|$)/);

    return match ? decodeURIComponent(match[1]) : null;
  }
}
