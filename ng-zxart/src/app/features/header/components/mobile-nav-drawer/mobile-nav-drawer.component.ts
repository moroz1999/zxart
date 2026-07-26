import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Dialog, DialogRef} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {CurrentRouteService} from '../../services/current-route.service';
import {MAIN_MENU, MenuEntry} from '../../../../shared/navigation/menu.config';
import {ThemeTriggerComponent} from '../theme-trigger/theme-trigger.component';
import {
  PictureSettingsTriggerComponent
} from '../../../picture-settings/components/picture-settings-trigger/picture-settings-trigger.component';
import {
  RatingsPanelDialogComponent
} from '../../../ratings/components/ratings-panel-dialog/ratings-panel-dialog.component';
import {
  CommentsPanelDialogComponent
} from '../../../comments/components/comments-panel-dialog/comments-panel-dialog.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-mobile-nav-drawer',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxPopoverMenuItemComponent,
    ThemeTriggerComponent,
    PictureSettingsTriggerComponent,
  ],
  templateUrl: './mobile-nav-drawer.component.html',
  styleUrls: ['./mobile-nav-drawer.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MobileNavDrawerComponent {
  readonly items: MenuEntry[] = MAIN_MENU;

  constructor(
    private routeService: CurrentRouteService,
    private iconReg: SvgIconRegistryService,
    private dialog: Dialog,
    private dialogRef: DialogRef<void>,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}x.svg`, 'mn-x')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'mn-star')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}chat.svg`, 'mn-chat')?.subscribe();
  }

  close(): void {
    this.dialogRef.close();
  }

  openRatings(): void {
    this.dialog.open(RatingsPanelDialogComponent, {
      panelClass: 'zx-dialog',
      backdropClass: 'zx-dialog-backdrop',
      width: '480px',
    });
  }

  openComments(): void {
    this.dialog.open(CommentsPanelDialogComponent, {
      panelClass: 'zx-dialog',
      backdropClass: 'zx-dialog-backdrop',
      width: '480px',
    });
  }

  isActive(item: MenuEntry): boolean {
    return this.routeService.isActive(item.url);
  }
}
