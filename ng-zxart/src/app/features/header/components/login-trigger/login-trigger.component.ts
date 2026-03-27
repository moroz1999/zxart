import {ChangeDetectionStrategy, ChangeDetectorRef, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CurrentUserService} from '../../../../shared/services/current-user.service';
import {BackendLinksService} from '../../services/backend-links.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxFormDirective} from '../../../../shared/directives/form/zx-form.directive';
import {ZxBodySmMutedDirective, ZxLinkDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-login-trigger',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    SvgIconComponent,
    ZxButtonComponent,
    ZxFormDirective,
    ZxBodySmMutedDirective,
    ZxLinkDirective,
    ZxPopoverMenuItemComponent,
    ZxHeaderPopoverComponent,
  ],
  templateUrl: './login-trigger.component.html',
  styleUrls: ['./login-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LoginTriggerComponent {
  readonly user$ = this.currentUserService.user$;
  readonly links$ = this.backendLinksService.links$;

  popoverOpen = false;

  userName = '';
  password = '';
  remember = true;
  loginError: string | null = null;
  loading = false;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(
    private currentUserService: CurrentUserService,
    private backendLinksService: BackendLinksService,
    private iconReg: SvgIconRegistryService,
    private cdr: ChangeDetectorRef,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
  }

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.popoverOpen = !this.popoverOpen;
    if (this.popoverOpen) {
      this.loginError = null;
    }
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  submitLogin(): void {
    if (this.loading || !this.userName || !this.password) {
      return;
    }
    this.loading = true;
    this.loginError = null;
    this.currentUserService.login(this.userName, this.password, this.remember).subscribe({
      next: () => {
        window.location.reload();
      },
      error: () => {
        this.loading = false;
        this.loginError = 'login.error';
        this.cdr.markForCheck();
      },
    });
  }

  submitLogout(): void {
    this.currentUserService.logout().subscribe(() => {
      window.location.reload();
    });
  }

  onOverlayKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
      this.closePopover();
    }
  }
}
