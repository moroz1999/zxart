import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {EntityRef} from '../../shared/models/entity-ref';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxCheckboxFieldComponent} from '../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxControlErrorsComponent} from '../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

/** Routed page for `author-alias/:id/edit`. */
@Component({
  selector: 'zx-author-alias-edit-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxCheckboxFieldComponent,
    ZxControlErrorsComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxEntityAutocompleteComponent,
    ZxStackComponent,
    ZxSpinnerComponent,
  ],
  templateUrl: './author-alias-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorAliasEditPageComponent implements OnInit, OnDestroy {
  readonly form: FormGroup = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    author: this.fb.control<EntityRef | null>(null),
    startDate: this.fb.nonNullable.control(''),
    endDate: this.fb.nonNullable.control(''),
    displayInMusic: this.fb.nonNullable.control(false),
    displayInGraphics: this.fb.nonNullable.control(false),
  });

  readonly titleMessages = {required: 'author-alias-form.error-title-required'};

  loading = true;
  submitting = false;
  errorMessage = '';

  private elementId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly formData: FormDataApiService,
    private readonly formSave: FormSaveApiService,
  ) {}

  ngOnInit(): void {
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.subscriptions.add(
      this.formData.load(this.elementId, ['authorId']).subscribe({
        next: data => {
          this.form.patchValue({
            title: String(data.fields['title'] ?? ''),
            author: data.refs['authorId'] ?? null,
            startDate: String(data.fields['startDate'] ?? ''),
            endDate: String(data.fields['endDate'] ?? ''),
            displayInMusic: !!Number(data.fields['displayInMusic']),
            displayInGraphics: !!Number(data.fields['displayInGraphics']),
          });
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'author-alias-form.error-load';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    this.subscriptions.add(
      this.formSave.save(this.elementId, {
        fields: {
          title: value.title,
          authorId: value.author ? String(value.author.id) : '',
          startDate: value.startDate,
          endDate: value.endDate,
          displayInMusic: value.displayInMusic ? '1' : '',
          displayInGraphics: value.displayInGraphics ? '1' : '',
        },
      }).subscribe({
        next: result => {
          this.router.navigateByUrl(`/author/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'author-alias-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
