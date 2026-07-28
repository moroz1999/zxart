import {ChangeDetectionStrategy, ChangeDetectorRef, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {CurrentUserService} from '../../../../shared/services/current-user.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxFormDirective} from '../../../../shared/ui/zx-form/zx-form.directive';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

@Component({
  selector: 'zx-login-popover-content',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxFormDirective,
    ZxPopoverMenuItemComponent,
    TextDirective,
  ],
  templateUrl: './login-popover-content.component.html',
  styleUrls: ['./login-popover-content.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LoginPopoverContentComponent {
  readonly user$ = this.currentUserService.user$;

  userName = '';
  password = '';
  remember = true;
  loginError: string | null = null;
  loading = false;

  constructor(
    private readonly currentUserService: CurrentUserService,
    private readonly changeDetectorRef: ChangeDetectorRef,
  ) {}

  submitLogin(): void {
    if (this.loading || this.userName === '' || this.password === '') {
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
        this.changeDetectorRef.markForCheck();
      },
    });
  }

  submitLogout(): void {
    this.currentUserService.logout().subscribe(() => {
      window.location.reload();
    });
  }
}
