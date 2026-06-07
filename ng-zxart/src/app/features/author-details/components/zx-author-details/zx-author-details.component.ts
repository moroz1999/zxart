import {ChangeDetectionStrategy, Component, ElementRef, Input, OnInit, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {AuthorCoreDto} from '../../models/author-core.dto';
import {AuthorCoreApiService} from '../../services/author-core-api.service';
import {ZxAuthorHeaderComponent} from '../zx-author-header/zx-author-header.component';
import {ZxAuthorCollaboratorsComponent} from '../zx-author-collaborators/zx-author-collaborators.component';
import {ZxAuthorMentionsComponent} from '../zx-author-mentions/zx-author-mentions.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxAuthorMiniDashboardComponent} from '../zx-author-mini-dashboard/zx-author-mini-dashboard.component';
import {ZxAuthorRatingsComponent} from '../zx-author-ratings/zx-author-ratings.component';
import {ZxAuthorCommentsComponent} from '../zx-author-comments/zx-author-comments.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxGridItemDirective} from '../../../../shared/ui/zx-grid/zx-grid-item.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {ZxAuthorGraphicsTabComponent} from '../zx-author-graphics-tab/zx-author-graphics-tab.component';
import {ZxAuthorMusicTabComponent} from '../zx-author-music-tab/zx-author-music-tab.component';
import {ZxAuthorSoftwareTabComponent} from '../zx-author-software-tab/zx-author-software-tab.component';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';

type AuthorTabId = 'best' | 'gfx' | 'music' | 'software' | 'collaborators' | 'mentions' | 'discussion';

@Component({
  selector: 'zx-author-details-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxBreadcrumbsComponent,
    ZxAuthorHeaderComponent,
    ZxAuthorCollaboratorsComponent,
    ZxStackComponent,
    ZxAuthorMiniDashboardComponent,
    ZxAuthorRatingsComponent,
    ZxAuthorCommentsComponent,
    ZxGridComponent,
    ZxGridItemDirective,
    ZxInlineComponent,
    ZxPanelComponent,
    ZxSkeletonBoneComponent,
    ZxTabsComponent,
    ZxTabComponent,
    ZxTabContentDirective,
    ZxAuthorGraphicsTabComponent,
    ZxAuthorMusicTabComponent,
    ZxAuthorSoftwareTabComponent,
    ZxAuthorMentionsComponent,
    CommentsListComponent,
  ],
  templateUrl: './zx-author-details.component.html',
  styleUrl: './zx-author-details.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorDetailsComponent implements OnInit {
  @Input() elementId = 0;
  @ViewChild(ZxTabsComponent, {read: ElementRef}) private tabsRef!: ElementRef<HTMLElement>;

  core$: Observable<AuthorCoreDto | null> = of(null);

  constructor(private readonly api: AuthorCoreApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      return;
    }
    this.core$ = this.api.getCore(+this.elementId).pipe(shareReplay(1));
  }

  getInitialTabIndex(core: AuthorCoreDto): number {
    const requestedTab = this.getRequestedTabId();
    const index = requestedTab ? this.getTabs(core).indexOf(requestedTab as AuthorTabId) : -1;

    return index >= 0 ? index : 0;
  }

  getTabHref(tabId: AuthorTabId): string {
    const path = window.location.pathname.replace(/\/tab:[^/]+(?=\/|$)/, '').replace(/\/page:\d+(?=\/|$)/, '');
    const normalizedPath = path.endsWith('/') ? path : `${path}/`;

    return `${normalizedPath}tab:${encodeURIComponent(tabId)}/`;
  }

  private getTabs(core: AuthorCoreDto): AuthorTabId[] {
    const tabs: AuthorTabId[] = [];

    if (core.tabs.hasPictures || core.tabs.hasTunes || core.tabs.hasProds) tabs.push('best');
    if (core.tabs.hasPictures) tabs.push('gfx');
    if (core.tabs.hasTunes) tabs.push('music');
    if (core.tabs.hasProds) tabs.push('software');

    if (core.tabs.hasCollaborators) tabs.push('collaborators');
    if (core.tabs.hasMentions) tabs.push('mentions');
    tabs.push('discussion');

    return tabs;
  }

  onTabChange(_: number): void {
    scrollToElementIfHidden(this.tabsRef?.nativeElement);
  }

  private getRequestedTabId(): string | null {
    const match = window.location.pathname.match(/\/tab:([^/]+)(?=\/|$)/);

    return match ? decodeURIComponent(match[1]) : null;
  }
}
