import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {Observable} from 'rxjs';
import {switchMap} from 'rxjs/operators';
import {MenuItem} from '../../models/menu-item';
import {MenuService} from '../../services/menu.service';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {CurrentRouteService} from '../../../header/services/current-route.service';
import {CurrentLanguageService} from '../../../header/services/current-language.service';

@Component({
  selector: 'zx-menu-block',
  standalone: true,
  imports: [
    CommonModule,
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
  readonly items$: Observable<MenuItem[]> = this.languageService.languageCode$.pipe(
    switchMap(code => this.menuService.getMenuItems(code)),
  );

  activeItem: MenuItem | null = null;
  activeTriggerWidth = 0;

  readonly positions: ConnectedPosition[] = [
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 0},
    {originX: 'start', originY: 'top', overlayX: 'start', overlayY: 'bottom', offsetY: 0},
  ];

  private closeTimer: ReturnType<typeof setTimeout> | null = null;

  constructor(
    private menuService: MenuService,
    private languageService: CurrentLanguageService,
    private routeService: CurrentRouteService,
    private cdr: ChangeDetectorRef,
  ) {}

  ngOnDestroy(): void {
    if (this.closeTimer !== null) {
      clearTimeout(this.closeTimer);
    }
  }

  isPopoverOpen(item: MenuItem): boolean {
    return this.activeItem === item;
  }

  openItem(item: MenuItem, triggerEl: HTMLElement): void {
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

  isActive(item: MenuItem): boolean {
    return this.routeService.isActive(item.url);
  }
}
