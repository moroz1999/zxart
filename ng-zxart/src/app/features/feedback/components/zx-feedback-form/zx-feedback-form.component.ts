import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  Input,
  numberAttribute,
  OnDestroy,
} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxControlErrorsComponent} from '../../../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxTextareaComponent} from '../../../../shared/ui/zx-textarea/zx-textarea.component';
import {FeedbackApiService} from '../../services/feedback-api.service';

@Component({
  selector: 'zx-feedback-form,zx-feedback-form-view',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxControlErrorsComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormDirective,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxInputComponent,
    ZxStackComponent,
    ZxTextareaComponent,
  ],
  templateUrl: './zx-feedback-form.component.html',
  styleUrl: './zx-feedback-form.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFeedbackFormComponent implements OnDestroy {
  @Input({transform: numberAttribute}) elementId = 0;

  readonly form: FormGroup;
  submitting = false;
  submitted = false;
  errorMessage = '';

  readonly nameMessages = {required: 'feedback.error-name'};
  readonly emailMessages = {required: 'feedback.error-email', email: 'feedback.error-email-format'};
  readonly messageMessages = {required: 'feedback.error-message', minlength: 'feedback.error-message'};

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly cdr: ChangeDetectorRef,
    private readonly translate: TranslateService,
    private readonly feedbackApiService: FeedbackApiService,
  ) {
    this.form = this.fb.group({
      name: ['', [Validators.required, Validators.maxLength(255)]],
      email: ['', [Validators.required, Validators.email, Validators.maxLength(255)]],
      message: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(10000)]],
    });
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSubmit(): void {
    if (this.elementId <= 0 || this.submitting) {
      return;
    }
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    this.cdr.markForCheck();

    this.subscriptions.add(
      this.feedbackApiService.submit(this.elementId, this.form.getRawValue()).subscribe({
        next: () => {
          this.submitting = false;
          this.submitted = true;
          this.form.reset();
          this.cdr.markForCheck();
        },
        error: (error: unknown) => {
          this.submitting = false;
          this.errorMessage = this.resolveErrorMessage(error);
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private resolveErrorMessage(error: unknown): string {
    if (error instanceof HttpErrorResponse && error.status === 422) {
      return this.translate.instant('feedback.error-email-rejected');
    }
    return this.translate.instant('feedback.error-send');
  }
}
