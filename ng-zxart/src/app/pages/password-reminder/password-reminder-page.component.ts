import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {PasswordReminderApiService} from '../../features/password-reminder/services/password-reminder-api.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxControlErrorsComponent} from '../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

@Component({
  selector: 'zx-password-reminder-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, TranslateModule, ZxButtonComponent, ZxControlErrorsComponent,
    ZxFormActionsComponent, ZxFormControlComponent, ZxFormFieldComponent, ZxFormLabelComponent,
    ZxFormMessageComponent, ZxFormDirective, ZxInputComponent, ZxStackComponent, HeadingDirective,
    ZxPageLayoutComponent],
  templateUrl: './password-reminder-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PasswordReminderPageComponent implements OnDestroy {
  readonly email = this.route.snapshot.queryParamMap.get('email') ?? '';
  readonly key = this.route.snapshot.queryParamMap.get('key') ?? '';
  readonly resetMode = this.email !== '' && this.key !== '';
  readonly form = this.fb.group({
    email: this.fb.nonNullable.control(this.email, [Validators.required, Validators.email]),
    password: this.fb.nonNullable.control('', Validators.required),
    passwordRepeat: this.fb.nonNullable.control('', Validators.required),
  });
  readonly emailMessages = {required: 'password-reminder.error-email', email: 'password-reminder.error-email'};
  readonly passwordMessages = {required: 'password-reminder.error-password'};
  submitting = false;
  message = '';
  success = false;
  private readonly subscriptions = new Subscription();

  constructor(private readonly route: ActivatedRoute, private readonly fb: FormBuilder,
    private readonly api: PasswordReminderApiService, private readonly cdr: ChangeDetectorRef) {}

  ngOnDestroy(): void { this.subscriptions.unsubscribe(); }

  onSubmit(): void {
    const requiredControl = this.resetMode ? this.form.controls.password : this.form.controls.email;
    if (requiredControl.invalid) { requiredControl.markAsTouched(); return; }
    const value = this.form.getRawValue();
    if (this.resetMode && value.password !== value.passwordRepeat) {
      this.success = false; this.message = 'password-reminder.error-password-match'; return;
    }
    this.submitting = true; this.message = '';
    const request$ = this.resetMode
      ? this.api.reset(this.email, this.key, value.password, value.passwordRepeat)
      : this.api.request(value.email);
    this.subscriptions.add(request$.subscribe(result => {
      this.submitting = false; this.success = result.success; this.message = result.message; this.cdr.markForCheck();
    }));
  }
}
