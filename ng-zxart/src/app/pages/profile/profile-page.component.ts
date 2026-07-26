import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, Subscription} from 'rxjs';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxItemDataComponent} from '../../shared/ui/zx-item-data/zx-item-data.component';
import {ZxItemDataItemComponent} from '../../shared/ui/zx-item-data/zx-item-data-item.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ProfileApiService} from '../../features/profile/services/profile-api.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Password fields, in order: control name → label key. */
const PASSWORD_FIELDS = [
  {field: 'currentPassword', labelKey: 'profile.current-password'},
  {field: 'password', labelKey: 'profile.new-password'},
  {field: 'passwordRepeat', labelKey: 'profile.repeat-password'},
] as const;

interface ProfileVm {
  readonly userName: string;
  readonly email: string;
  readonly loading: boolean;
  readonly authenticated: boolean;
  readonly submitting: boolean;
  readonly changed: boolean;
  readonly errorMessage: string;
}

const INITIAL_VM: ProfileVm = {
  userName: '',
  email: '',
  loading: true,
  authenticated: false,
  submitting: false,
  changed: false,
  errorMessage: '',
};

/**
 * Routed page for `profile` — the current user's own account. Name and email
 * identify the account and are shown read-only; the password is the only thing
 * an account may change about itself.
 */
@Component({
  selector: 'zx-profile-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxItemDataComponent,
    ZxItemDataItemComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './profile-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProfilePageComponent implements OnInit, OnDestroy {
  readonly passwordFields = PASSWORD_FIELDS;

  readonly form = this.fb.group({
    currentPassword: this.fb.nonNullable.control(''),
    password: this.fb.nonNullable.control(''),
    passwordRepeat: this.fb.nonNullable.control(''),
  });

  private readonly state = new BehaviorSubject<ProfileVm>(INITIAL_VM);
  readonly vm$ = this.state.asObservable();

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly api: ProfileApiService,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(
      this.api.getProfile().subscribe(profile => this.patchState({
        loading: false,
        authenticated: profile !== null,
        userName: profile?.userName ?? '',
        email: profile?.email ?? '',
      })),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSubmit(): void {
    this.patchState({submitting: true, changed: false, errorMessage: ''});
    this.subscriptions.add(
      this.api.changePassword(this.form.getRawValue()).subscribe({
        next: profile => {
          if (profile === null) {
            this.patchState({submitting: false, errorMessage: 'profile.error-save'});
            return;
          }
          this.form.reset();
          this.patchState({submitting: false, changed: true});
        },
        error: (err: HttpErrorResponse) => this.patchState({
          submitting: false,
          errorMessage: err.error?.errorMessage ?? 'profile.error-save',
        }),
      }),
    );
  }

  private patchState(patch: Partial<ProfileVm>): void {
    this.state.next({...this.state.getValue(), ...patch});
  }
}
