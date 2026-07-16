import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
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
import {RegistrationApiService} from '../../features/registration/services/registration-api.service';

/** Optional text fields shown below the required ones: control → label key. */
const OPTIONAL_FIELDS = [
  {field: 'firstName', labelKey: 'register.first-name'},
  {field: 'lastName', labelKey: 'register.last-name'},
  {field: 'company', labelKey: 'register.company'},
  {field: 'website', labelKey: 'register.website'},
  {field: 'phone', labelKey: 'register.phone'},
  {field: 'address', labelKey: 'register.address'},
  {field: 'city', labelKey: 'register.city'},
  {field: 'postIndex', labelKey: 'register.post-index'},
  {field: 'country', labelKey: 'register.country'},
] as const;

/** Routed page for `register` — static self-service registration. */
@Component({
  selector: 'zx-register-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxControlErrorsComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxStackComponent,
  ],
  templateUrl: './register-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RegisterPageComponent implements OnDestroy {
  readonly optionalFields = OPTIONAL_FIELDS;

  readonly form: FormGroup = this.fb.group({
    userName: this.fb.nonNullable.control('', Validators.required),
    email: this.fb.nonNullable.control('', [Validators.required, Validators.email]),
    password: this.fb.nonNullable.control('', Validators.required),
    passwordRepeat: this.fb.nonNullable.control('', Validators.required),
    firstName: this.fb.nonNullable.control(''),
    lastName: this.fb.nonNullable.control(''),
    company: this.fb.nonNullable.control(''),
    website: this.fb.nonNullable.control(''),
    phone: this.fb.nonNullable.control(''),
    address: this.fb.nonNullable.control(''),
    city: this.fb.nonNullable.control(''),
    postIndex: this.fb.nonNullable.control(''),
    country: this.fb.nonNullable.control(''),
  });

  readonly userNameMessages = {required: 'register.error-username'};
  readonly emailMessages = {required: 'register.error-email', email: 'register.error-email-format'};
  readonly passwordMessages = {required: 'register.error-password'};

  submitting = false;
  errorMessage = '';
  successMessage = '';

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly cdr: ChangeDetectorRef,
    private readonly api: RegistrationApiService,
  ) {}

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    const value = this.form.getRawValue();
    if (value.password !== value.passwordRepeat) {
      this.errorMessage = 'register.error-password-match';
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    this.successMessage = '';
    this.subscriptions.add(
      this.api.register(value).subscribe({
        next: result => {
          this.submitting = false;
          if (result.success) {
            this.successMessage = result.message;
            this.form.reset();
          } else {
            this.errorMessage = result.message;
          }
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? err.error?.message ?? 'register.error-generic';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
