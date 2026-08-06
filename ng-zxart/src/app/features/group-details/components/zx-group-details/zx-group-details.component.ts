import {ChangeDetectionStrategy, Component, ElementRef, EventEmitter, Input, OnChanges, Output, SimpleChanges, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay, tap} from 'rxjs/operators';
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
import {BreadcrumbService} from '../../../../shared/services/breadcrumb.service';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';
import {PageMetadataService} from '../../../../shared/services/page-metadata.service';
type GroupTabId = 'overview' | 'works' | 'group' | 'connections' | 'media' | 'discussion';

@Component({
  selector: 'zx-group-details-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
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
export class ZxGroupDetailsComponent implements OnChanges {
  @Input() elementId = 0;
  /** Active tab id from the route (`group/:id/:tab`); null = default tab. */
  @Input() activeTab: string | null = null;
  @Output() pageTitleChange = new EventEmitter<string>();
  @ViewChild(ZxTabsComponent, {read: ElementRef}) private tabsRef!: ElementRef<HTMLElement>;

  core$: Observable<GroupCoreDto | null> = of(null);

  constructor(
    private readonly api: GroupCoreApiService,
    private readonly breadcrumbService: BreadcrumbService,
    private readonly pageMetadata: PageMetadataService,
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (!changes['elementId']) {
      return;
    }
    if (!this.elementId || +this.elementId <= 0) {
      this.core$ = of(null);
      return;
    }
    this.core$ = this.api.getCore(+this.elementId).pipe(
      tap(core => {
        if (core) {
          this.pageTitleChange.emit(core.title);
          this.pageMetadata.applyPlainTitle(core.title);
          this.breadcrumbService.setEntityTrail({items: core.breadcrumbs, currentTitle: core.title});
        } else {
          this.breadcrumbService.setNotFoundTrail();
        }
      }),
      shareReplay(1),
    );
  }

  getInitialTabIndex(core: GroupCoreDto): number {
    const index = this.activeTab ? this.getTabs(core).indexOf(this.activeTab as GroupTabId) : -1;

    return index >= 0 ? index : 0;
  }

  getTabHref(tabId: GroupTabId): string {
    return `/group/${this.elementId}/${encodeURIComponent(tabId)}`;
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
}
