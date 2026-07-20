import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule} from '@angular/forms';
import {Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxCheckboxFieldComponent} from '../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ProfileApiService} from '../../features/profile/services/profile-api.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Text fields shown on the profile edit form: control name → label key. */
const TEXT_FIELDS = [
  {field: 'firstName', labelKey: 'profile.first-name'},
  {field: 'lastName', labelKey: 'profile.last-name'},
  {field: 'company', labelKey: 'profile.company'},
  {field: 'email', labelKey: 'profile.email'},
  {field: 'phone', labelKey: 'profile.phone'},
  {field: 'website', labelKey: 'profile.website'},
  {field: 'address', labelKey: 'profile.address'},
  {field: 'city', labelKey: 'profile.city'},
  {field: 'postIndex', labelKey: 'profile.post-index'},
  {field: 'country', labelKey: 'profile.country'},
] as const;

/** Routed page for `profile/edit`. */
@Component({
  selector: 'zx-profile-edit-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxCheckboxFieldComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxStackComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './profile-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProfileEditPageComponent implements OnInit, OnDestroy {
  readonly textFields = TEXT_FIELDS;

  readonly form: FormGroup = this.fb.group({
    firstName: this.fb.nonNullable.control(''),
    lastName: this.fb.nonNullable.control(''),
    company: this.fb.nonNullable.control(''),
    email: this.fb.nonNullable.control(''),
    phone: this.fb.nonNullable.control(''),
    website: this.fb.nonNullable.control(''),
    address: this.fb.nonNullable.control(''),
    city: this.fb.nonNullable.control(''),
    postIndex: this.fb.nonNullable.control(''),
    country: this.fb.nonNullable.control(''),
    subscribe: this.fb.nonNullable.control(false),
    showemail: this.fb.nonNullable.control(false),
  });

  loading = true;
  submitting = false;
  errorMessage = '';

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly api: ProfileApiService,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(
      this.api.getProfile().subscribe(profile => {
        if (profile) {
          this.form.patchValue(profile);
        } else {
          this.errorMessage = 'profile.please-log-in';
        }
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSubmit(): void {
    this.submitting = true;
    this.errorMessage = '';
    this.subscriptions.add(
      this.api.saveProfile(this.form.getRawValue()).subscribe({
        next: () => this.router.navigateByUrl('/profile'),
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'profile.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
