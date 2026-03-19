import {Component, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {Subscription} from 'rxjs';
import {CurrentUserService} from '../../../../shared/services/current-user.service';
import {CurrentUser} from '../../../../shared/models/current-user';
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
})
export class LoginTriggerComponent implements OnInit, OnDestroy {
  user: CurrentUser | null = null;
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

  private subscription = new Subscription();

  constructor(
    private currentUserService: CurrentUserService,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
    this.subscription.add(
      this.currentUserService.user$.subscribe(user => {
        this.user = user;
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  get isLoggedIn(): boolean {
    return this.user !== null && this.user.userName !== 'anonymous';
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
