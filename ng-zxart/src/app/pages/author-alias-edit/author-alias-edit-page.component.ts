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
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';
import {PageMetadataService} from '../../shared/services/page-metadata.service';
import {AuthorAliasFormApiService} from '../../features/author-details/services/author-alias-form-api.service';

/** Routed page for editing an alias or creating one for a selected author. */
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
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './author-alias-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorAliasEditPageComponent implements OnInit, OnDestroy {
  readonly form: FormGroup = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    author: this.fb.control<EntityRef | null>(null, Validators.required),
    startDate: this.fb.nonNullable.control(''),
    endDate: this.fb.nonNullable.control(''),
    displayInMusic: this.fb.nonNullable.control(false),
    displayInGraphics: this.fb.nonNullable.control(false),
  });

  readonly titleMessages = {required: 'author-alias-form.error-title-required'};
  readonly authorMessages = {required: 'author-alias-form.error-author-required'};

  creating = false;
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
    private readonly pageMetadata: PageMetadataService,
    private readonly authorAliasFormApi: AuthorAliasFormApiService,
  ) {}

  ngOnInit(): void {
    const routeId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.creating = this.route.snapshot.data['create'] === true;
    if (this.creating) {
      this.subscriptions.add(
        this.authorAliasFormApi.getForm(routeId).subscribe({
          next: data => {
            if (data.errorMessage) {
              this.loading = false;
              this.errorMessage = data.errorMessage;
              this.cdr.markForCheck();
              return;
            }
            this.form.patchValue({author: data.author});
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
      return;
    }

    this.elementId = routeId;
    this.subscriptions.add(
      this.formData.load(this.elementId, ['authorId']).subscribe({
        next: data => {
          if (data.errorMessage) {
            this.loading = false;
            this.errorMessage = data.errorMessage;
            this.cdr.markForCheck();
            return;
          }
          this.pageMetadata.applyFormTitle(this.route.snapshot, data.entityTitle);
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
    const fields = {
      authorId: value.author ? Number(value.author.id) : 0,
      title: value.title,
      startDate: value.startDate,
      endDate: value.endDate,
      displayInMusic: value.displayInMusic,
      displayInGraphics: value.displayInGraphics,
    };
    const save$ = this.creating
      ? this.authorAliasFormApi.create(fields)
      : this.formSave.save(this.elementId, {
        fields: {
          title: fields.title,
          authorId: String(fields.authorId),
          startDate: fields.startDate,
          endDate: fields.endDate,
          displayInMusic: fields.displayInMusic ? '1' : '',
          displayInGraphics: fields.displayInGraphics ? '1' : '',
        },
      });
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'author-alias-form.error-save';
            this.cdr.markForCheck();
            return;
          }
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
