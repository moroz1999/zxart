import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {EntityRef} from '../../shared/models/entity-ref';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxControlErrorsComponent} from '../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ImageUploadChange, ZxImageUploadComponent} from '../../shared/ui/zx-image-upload/zx-image-upload.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxDeleteEntityButtonComponent} from '../../shared/ui/zx-delete-entity-button/zx-delete-entity-button.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';
import {PageMetadataService} from '../../shared/services/page-metadata.service';

/** Routed page for `party/:id/edit`. */
@Component({
  selector: 'zx-party-edit-page',
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
    ZxEntityAutocompleteComponent,
    ZxImageUploadComponent,
    ZxFormSectionComponent,
    ZxButtonControlsComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
    ZxDeleteEntityButtonComponent,
  ],
  templateUrl: './party-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PartyEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    abbreviation: this.fb.nonNullable.control(''),
    country: this.fb.control<EntityRef | null>(null),
    city: this.fb.control<EntityRef | null>(null),
  });

  readonly titleMessages = {required: 'party-form.error-title-required'};

  loading = true;
  submitting = false;
  errorMessage = '';
  imageUrl: string | null = null;
  creating = false;

  /** Where the user lands once the party is deleted. */
  readonly deleteReturnUrl = '/parties';

  elementId = 0;
  private year = 0;
  private returnUrl = '/parties';
  private imageFile: File | null = null;
  private removeImage = false;
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
    this.creating = this.route.snapshot.data['create'] === true;
    if (this.creating) {
      this.year = Number(this.route.snapshot.paramMap.get('year')) || 0;
      this.returnUrl = `/parties/${this.year}`;
      this.loading = false;
      return;
    }

    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.returnUrl = `/party/${this.elementId}`;
    this.subscriptions.add(
      this.formData.load(this.elementId, ['country', 'city']).subscribe({
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
            abbreviation: String(data.fields['abbreviation'] ?? ''),
            country: data.refs['country'] ?? null,
            city: data.refs['city'] ?? null,
          });
          this.imageUrl = data.images['image'] ?? null;
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'party-form.error-load';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCancel(): void {
    this.router.navigateByUrl(this.returnUrl);
  }

  onImageChanged(change: ImageUploadChange): void {
    this.imageFile = change.file;
    this.removeImage = change.removed;
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    const payload = {
        fields: {
          title: value.title,
          abbreviation: value.abbreviation,
          country: value.country ? String(value.country.id) : '',
          city: value.city ? String(value.city.id) : '',
        },
        image: {field: 'image', file: this.imageFile, remove: this.removeImage},
      };
    const save$ = this.creating
      ? this.formSave.create('party', payload, this.year)
      : this.formSave.save(this.elementId, payload);
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'party-form.error-save';
            this.cdr.markForCheck();
            return;
          }
          this.router.navigateByUrl(`/party/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'party-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
