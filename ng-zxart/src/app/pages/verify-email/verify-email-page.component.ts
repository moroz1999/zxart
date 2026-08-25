import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {VerifyEmailApiService} from '../../features/verify-email/services/verify-email-api.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/**
 * Routed page for `verify-email` — the target of the registration email's link.
 * The visitor already acted by opening the link, so the page applies it on load
 * instead of asking for another click.
 */
@Component({
  selector: 'zx-verify-email-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxButtonComponent,
    ZxFormMessageComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './verify-email-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class VerifyEmailPageComponent implements OnInit, OnDestroy {
  pending = true;
  success = false;
  message = '';

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly route: ActivatedRoute,
    private readonly api: VerifyEmailApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    const params = this.route.snapshot.queryParamMap;
    const email = params.get('email') ?? '';
    const key = params.get('key') ?? '';
    if (email === '' || key === '') {
      this.pending = false;
      this.message = 'verify-email.error-invalid';
      return;
    }

    this.subscriptions.add(
      this.api.verify(email, key).subscribe(result => {
        this.pending = false;
        this.success = result.success;
        this.message = result.message;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }
}
