import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CurrentUserService} from '../../../../shared/services/current-user.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {environment} from '../../../../../environments/environment';
import {
  LoginPopoverContentComponent
} from '../login-popover-content/login-popover-content.component';

@Component({
  selector: 'zx-login-trigger',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    SvgIconComponent,
    ZxButtonComponent,
    ZxHeaderPopoverComponent,
    LoginPopoverContentComponent,
  ],
  templateUrl: './login-trigger.component.html',
  styleUrls: ['./login-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LoginTriggerComponent {
  readonly user$ = this.currentUserService.user$;

  popoverOpen = false;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(
    private currentUserService: CurrentUserService,
    private iconReg: SvgIconRegistryService,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
  }

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.popoverOpen = !this.popoverOpen;
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  onOverlayKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
      this.closePopover();
    }
  }
}
