import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {MAIN_MENU, MenuEntry} from '../../menu.config';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {CurrentRouteService} from '../../../header/services/current-route.service';
import {isSpaUrl} from '../../../../shared/utils/spa-url';


import {NavigationEnd, Router, RouterLink} from '@angular/router';
import {Subscription, filter} from 'rxjs';@Component({
  selector: 'zx-menu-block',
  standalone: true,
  imports: [RouterLink, 
    CommonModule,
    TranslateModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxButtonComponent,
    ZxPopoverMenuItemComponent,
    ZxHeaderPopoverComponent,
  ],
  templateUrl: './menu-block.component.html',
  styleUrls: ['./menu-block.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MenuBlockComponent implements OnDestroy {
  readonly items: MenuEntry[] = MAIN_MENU;

  activeItem: MenuEntry | null = null;
  activeTriggerWidth = 0;

  readonly positions: ConnectedPosition[] = [
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 0},
    {originX: 'start', originY: 'top', overlayX: 'start', overlayY: 'bottom', offsetY: 0},
  ];

  private closeTimer: ReturnType<typeof setTimeout> | null = null;
  private readonly routerSub: Subscription;

  constructor(
    private routeService: CurrentRouteService,
    private cdr: ChangeDetectorRef,
    router: Router,
  ) {
    // Active highlighting is derived from the router URL; re-check it after each
    // navigation so the highlight follows client-side route changes.
    this.routerSub = router.events
      .pipe(filter(event => event instanceof NavigationEnd))
      .subscribe(() => this.cdr.markForCheck());
  }

  ngOnDestroy(): void {
    if (this.closeTimer !== null) {
      clearTimeout(this.closeTimer);
    }
    this.routerSub.unsubscribe();
  }

  isPopoverOpen(item: MenuEntry): boolean {
    return this.activeItem === item;
  }

  openItem(item: MenuEntry, triggerEl: HTMLElement): void {
    this.cancelClose();
    if (item.children.length > 0) {
      this.activeTriggerWidth = triggerEl.offsetWidth;
      this.activeItem = item;
    }
  }

  scheduleClose(): void {
    this.closeTimer = setTimeout(() => {
      this.activeItem = null;
      this.closeTimer = null;
      this.cdr.markForCheck();
    }, 120);
  }

  cancelClose(): void {
    if (this.closeTimer !== null) {
      clearTimeout(this.closeTimer);
      this.closeTimer = null;
    }
  }

  isActive(item: MenuEntry): boolean {
    return this.routeService.isActive(item.url);
  }

  isInternal(url: string): boolean {
    return isSpaUrl(url);
  }
}
