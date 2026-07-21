import {ChangeDetectionStrategy, Component, ElementRef, EventEmitter, Input, OnChanges, Output, SimpleChanges, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay, tap} from 'rxjs/operators';
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
import {BreadcrumbService} from '../../../../shared/services/breadcrumb.service';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';
import {RouterLink} from '@angular/router';
/** Static tab ids; competition tabs use their raw `compoType` as the id. */
type PartyTabId = 'overview' | 'activity' | string;

@Component({
  selector: 'zx-party-details-view',
  standalone: true,
  imports: [RouterLink, 
    CommonModule,
    TranslateModule,
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
export class ZxPartyDetailsComponent implements OnChanges {
  @Input() elementId = 0;
  /** Active tab id from the route (`party/:id/:tab`); null = default tab. */
  @Input() activeTab: string | null = null;
  @Output() pageTitleChange = new EventEmitter<string>();
  @ViewChild(ZxTabsComponent, {read: ElementRef}) private tabsRef!: ElementRef<HTMLElement>;

  readonly skeletonTabs = [0, 1, 2, 3];

  core$: Observable<PartyCoreDto | null> = of(null);

  constructor(
    private readonly api: PartyCoreApiService,
    private readonly breadcrumbService: BreadcrumbService,
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
          this.breadcrumbService.setEntityTrail({items: core.breadcrumbs, currentTitle: core.title});
        }
      }),
      shareReplay(1),
    );
  }

  getInitialTabIndex(core: PartyCoreDto): number {
    const index = this.activeTab ? this.getTabs(core).indexOf(this.activeTab) : -1;

    return index >= 0 ? index : 0;
  }

  getTabHref(tabId: PartyTabId): string {
    return `/party/${this.elementId}/${encodeURIComponent(tabId)}`;
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
}
