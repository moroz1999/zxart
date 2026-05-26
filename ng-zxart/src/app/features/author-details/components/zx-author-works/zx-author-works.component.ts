import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {AuthorTabsDto} from '../../models/author-core.dto';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {ZxAuthorGraphicsTabComponent} from '../zx-author-graphics-tab/zx-author-graphics-tab.component';
import {ZxAuthorMusicTabComponent} from '../zx-author-music-tab/zx-author-music-tab.component';
import {ZxAuthorSoftwareTabComponent} from '../zx-author-software-tab/zx-author-software-tab.component';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxPictureGridSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {ZxTuneTableSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';

@Component({
  selector: 'zx-author-works',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTabsComponent,
    ZxTabComponent,
    ZxTabContentDirective,
    ZxAuthorGraphicsTabComponent,
    ZxAuthorMusicTabComponent,
    ZxAuthorSoftwareTabComponent,
    InViewportDirective,
    ZxPanelComponent,
    ZxPictureGridSkeletonComponent,
    ZxTuneTableSkeletonComponent,
    ZxProdsListSkeletonComponent,
  ],
  templateUrl: './zx-author-works.component.html',
  styleUrl: './zx-author-works.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorWorksComponent implements OnInit {
  @Input() elementId = 0;
  @Input() tabs!: AuthorTabsDto;

  initialTabIndex = 0;
  baseUrl = '';
  gfxHref = '';
  musicHref = '';
  softwareHref = '';
  mounted = false;

  ngOnInit(): void {
    this.baseUrl = this.parseBaseUrl();
    this.gfxHref = this.baseUrl + 'tab:gfx/';
    this.musicHref = this.baseUrl + 'tab:music/';
    this.softwareHref = this.baseUrl + 'tab:software/';
    this.initialTabIndex = this.parseInitialTabIndex();
  }

  onInViewport(): void {
    this.mounted = true;
  }

  private parseBaseUrl(): string {
    let path = window.location.pathname;
    path = path.replace(/\/tab:[^/]+/g, '');
    path = path.replace(/\/page:\d+/g, '');
    return path.endsWith('/') ? path : path + '/';
  }

  private parseInitialTabIndex(): number {
    const match = window.location.pathname.match(/\/tab:([^/]+)/);
    if (!match) return 0;
    const key = match[1];
    const keys: string[] = [];
    if (this.tabs.hasPictures) keys.push('gfx');
    if (this.tabs.hasTunes) keys.push('music');
    if (this.tabs.hasProds) keys.push('software');
    const idx = keys.indexOf(key);
    return idx >= 0 ? idx : 0;
  }
}
