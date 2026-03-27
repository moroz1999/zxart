import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {shareReplay, switchMap} from 'rxjs/operators';
import {Dialog, DialogRef} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {MenuService} from '../../../menu/services/menu.service';
import {CurrentRouteService} from '../../services/current-route.service';
import {CurrentLanguageService} from '../../services/current-language.service';
import {MenuItem} from '../../../menu/models/menu-item';
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
  readonly items$: Observable<MenuItem[]> = this.languageService.languageCode$.pipe(
    switchMap(code => this.menuService.getMenuItems(code)),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private menuService: MenuService,
    private languageService: CurrentLanguageService,
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
      panelClass: 'zx-panel-dialog',
      backdropClass: 'zx-dialog-backdrop',
    });
  }

  openComments(): void {
    this.dialog.open(CommentsPanelDialogComponent, {
      panelClass: 'zx-panel-dialog',
      backdropClass: 'zx-dialog-backdrop',
    });
  }

  isActive(item: MenuItem): boolean {
    return this.routeService.isActive(item.url);
  }
}
