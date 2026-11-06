import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription, take} from 'rxjs';
import {ManageUserGroupsApiService} from '../../features/manage-users/services/manage-user-groups-api.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxControlErrorsComponent} from '../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxTextareaComponent} from '../../shared/ui/zx-textarea/zx-textarea.component';

const LIST_URL = '/manage/user-groups';

/**
 * Create or edit a user group (`/manage/user-groups/add`, `/manage/user-groups/:id`).
 * What the group's members may do is set in the admin panel, not here.
 */
@Component({
  selector: 'zx-manage-user-group-edit-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    HeadingDirective,
    ZxButtonComponent,
    ZxControlErrorsComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxPageLayoutComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    ZxTextareaComponent,
  ],
  templateUrl: './manage-user-group-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageUserGroupEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.nonNullable.group({
    groupName: ['', Validators.required],
    description: [''],
  });

  readonly nameMessages = {required: 'manage-user-groups.error-name-required'};

  creating = false;
  loading = true;
  submitting = false;
  errorMessage = '';

  private groupId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly api: ManageUserGroupsApiService,
  ) {}

  ngOnInit(): void {
    this.creating = this.route.snapshot.data['create'] === true;
    this.groupId = Number(this.route.snapshot.paramMap.get('id')) || 0;

    this.subscriptions.add(
      this.api.groups$.pipe(take(1)).subscribe(groups => {
        const group = groups.find(item => item.id === this.groupId);
        if (group) {
          this.form.setValue({groupName: group.groupName, description: group.description});
        }
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
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
    this.errorMessage = '';
    const request = this.form.getRawValue();
    const save$ = this.creating ? this.api.create(request) : this.api.update(this.groupId, request);

    this.subscriptions.add(
      save$.subscribe({
        next: () => this.router.navigateByUrl(LIST_URL),
        error: (error: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = error.error?.errorMessage ?? 'manage-user-groups.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
