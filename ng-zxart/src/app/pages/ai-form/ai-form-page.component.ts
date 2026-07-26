import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxCheckboxFieldComponent} from '../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';
import {PageMetadataService} from '../../shared/services/page-metadata.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

interface AiField {
  field: string;
  labelKey: string;
}

/**
 * Generic AI re-queue form (`<entity>/:id/ai`). Renders a checkbox per AI task
 * (with its current queue status) and posts the legacy `receiveAiForm` action to
 * re-queue the checked ones. Field list + entity path come from the route data.
 */
@Component({
  selector: 'zx-ai-form-page',
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
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxStackComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './ai-form-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AiFormPageComponent implements OnInit, OnDestroy {
  form: FormGroup = this.fb.group({});
  fields: AiField[] = [];
  statuses: Record<string, string> = {};
  loading = true;
  submitting = false;
  errorMessage = '';

  private elementId = 0;
  private entityPath = '';
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly formData: FormDataApiService,
    private readonly formSave: FormSaveApiService,
    private readonly pageMetadata: PageMetadataService,
  ) {}

  ngOnInit(): void {
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.entityPath = this.route.snapshot.data['entityPath'] ?? '';
    this.fields = (this.route.snapshot.data['fields'] ?? []) as AiField[];
    for (const item of this.fields) {
      this.form.addControl(item.field, this.fb.nonNullable.control(false));
    }

    this.subscriptions.add(
      this.formData.load(this.elementId).subscribe({
        next: data => {
          this.pageMetadata.applyFormTitle(this.route.snapshot, data.entityTitle);
          this.statuses = data.aiStatuses;
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'ai-form.error-load';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSubmit(): void {
    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    const fields: Record<string, string> = {};
    for (const item of this.fields) {
      fields[item.field] = value[item.field] ? '1' : '';
    }
    this.subscriptions.add(
      this.formSave.save(this.elementId, {fields}, 'receiveAiForm').subscribe({
        next: result => {
          this.router.navigateByUrl(`/${this.entityPath}/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'ai-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
