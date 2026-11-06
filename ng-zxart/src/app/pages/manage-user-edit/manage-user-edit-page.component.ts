import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router, RouterLink} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, Subscription, switchMap, take} from 'rxjs';
import {ManageUserDetailsDto, ManageUserGroupDto, ManageUserSaveRequest} from '../../features/manage-users/models/manage-users.dto';
import {ManageUserGroupsApiService} from '../../features/manage-users/services/manage-user-groups-api.service';
import {ManageUsersApiService} from '../../features/manage-users/services/manage-users-api.service';
import {PRIVILEGE_USER_BAN, RootPrivilegeService} from '../../shared/services/root-privilege.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxCalloutComponent} from '../../shared/ui/zx-callout/zx-callout.component';
import {ZxCheckboxFieldComponent} from '../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxCheckboxGroupComponent} from '../../shared/ui/zx-checkbox-group/zx-checkbox-group.component';
import {ConfirmDialogService} from '../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {ZxFactComponent} from '../../shared/ui/zx-facts/zx-fact.component';
import {ZxFactsComponent} from '../../shared/ui/zx-facts/zx-facts.component';
import {ZxControlErrorsComponent} from '../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

const LIST_URL = '/manage/users';

/**
 * One user (`/manage/users/:id`): what they have done on the site, the form
 * of their account and their groups, and the ban.
 *
 * A group membership takes effect at the user's next sign-in: privileges are
 * worked out once per session.
 */
@Component({
  selector: 'zx-manage-user-edit-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    TranslateModule,
    HeadingDirective,
    TextDirective,
    ZxButtonComponent,
    ZxCalloutComponent,
    ZxCheckboxFieldComponent,
    ZxCheckboxGroupComponent,
    ZxControlErrorsComponent,
    ZxFactComponent,
    ZxFactsComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormSectionComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxPageLayoutComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
  ],
  templateUrl: './manage-user-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageUserEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.nonNullable.group({
    userName: ['', Validators.required],
    email: [''],
    verified: [false],
    commentsAllowed: [false],
    showEmail: [false],
    vip: [false],
    supporter: [false],
    volunteer: [false],
  });

  /** One checkbox per user group, keyed by the group id. */
  readonly groupsForm = new FormGroup<Record<string, FormControl<boolean>>>({});

  readonly userNameMessages = {required: 'manage-users.error-name-required'};
  readonly mayBan$ = this.rootPrivilegeService.has(PRIVILEGE_USER_BAN);

  details: ManageUserDetailsDto | null = null;
  groups: ManageUserGroupDto[] = [];
  loading = true;
  notFound = false;
  submitting = false;
  saved = false;
  errorMessage = '';

  private userId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly api: ManageUsersApiService,
    private readonly groupsApi: ManageUserGroupsApiService,
    private readonly rootPrivilegeService: RootPrivilegeService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.userId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.subscriptions.add(
      combineLatest([this.api.getDetails(this.userId), this.groupsApi.groups$.pipe(take(1))]).subscribe({
        next: ([details, groups]) => {
          this.groups = groups;
          for (const group of groups) {
            this.groupsForm.addControl(String(group.id), this.fb.nonNullable.control(false));
          }
          this.applyDetails(details);
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.notFound = true;
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  trackByGroup(_index: number, group: ManageUserGroupDto): number {
    return group.id;
  }

  onCancel(): void {
    this.router.navigateByUrl(LIST_URL);
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting = true;
    this.saved = false;
    this.errorMessage = '';
    this.subscriptions.add(
      this.api.update(this.userId, this.buildRequest()).subscribe({
        next: details => {
          this.applyDetails(details);
          this.submitting = false;
          this.saved = true;
          // member counts of the groups list follow the change
          this.groupsApi.refresh();
          this.cdr.markForCheck();
        },
        error: (error: HttpErrorResponse) => this.showError(error, 'manage-users.error-save'),
      }),
    );
  }

  onBan(): void {
    const name = this.details?.userName ?? '';
    const keys = ['manage-users.ban-confirm-title', 'manage-users.ban-confirm-message', 'manage-users.ban', 'form.cancel'];
    this.subscriptions.add(
      this.translate.get(keys, {name}).pipe(
        switchMap((texts: Record<string, string>) => this.confirmDialog.confirm({
          title: texts['manage-users.ban-confirm-title'],
          message: texts['manage-users.ban-confirm-message'],
          confirmLabel: texts['manage-users.ban'],
          cancelLabel: texts['form.cancel'],
          danger: true,
        })),
      ).subscribe(confirmed => {
        if (confirmed) {
          this.runBan();
        }
      }),
    );
  }

  private runBan(): void {
    this.submitting = true;
    this.saved = false;
    this.errorMessage = '';
    this.subscriptions.add(
      this.api.ban(this.userId).subscribe({
        next: details => {
          this.applyDetails(details);
          this.submitting = false;
          this.cdr.markForCheck();
        },
        error: (error: HttpErrorResponse) => this.showError(error, 'manage-users.error-ban'),
      }),
    );
  }

  private showError(error: HttpErrorResponse, fallbackKey: string): void {
    this.submitting = false;
    this.errorMessage = error.error?.errorMessage ?? fallbackKey;
    this.cdr.markForCheck();
  }

  private applyDetails(details: ManageUserDetailsDto): void {
    this.details = details;
    this.form.setValue({
      userName: details.userName,
      email: details.email,
      verified: details.verified,
      commentsAllowed: details.commentsAllowed,
      showEmail: details.showEmail,
      vip: details.vip,
      supporter: details.supporter,
      volunteer: details.volunteer,
    });
    const memberOf = new Set(details.groupIds);
    for (const group of this.groups) {
      this.groupsForm.controls[String(group.id)]?.setValue(memberOf.has(group.id));
    }
  }

  private buildRequest(): ManageUserSaveRequest {
    const groupIds = this.groups
      .filter(group => this.groupsForm.controls[String(group.id)]?.value === true)
      .map(group => group.id);

    return {...this.form.getRawValue(), groupIds};
  }
}
