import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {EntityRef} from '../../shared/models/entity-ref';
import {FormLanguage} from '../../shared/models/form-data-response';
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
import {ZxImageUploadComponent, ImageUploadChange} from '../../shared/ui/zx-image-upload/zx-image-upload.component';
import {ZxMultilangFieldComponent} from '../../shared/ui/zx-multilang-field/zx-multilang-field.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxCheckboxGroupComponent} from '../../shared/ui/zx-checkbox-group/zx-checkbox-group.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';
import {PageMetadataService} from '../../shared/services/page-metadata.service';

/** Tech fields shown nowhere in the UI but preserved on save (passthrough). */
const PASSTHROUGH_FIELDS = ['chipType', 'channelsType', 'frequency', 'intFrequency', 'palette'];

/** Routed page for `author/:id/edit`. */
@Component({
  selector: 'zx-author-edit-page',
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
    ZxImageUploadComponent,
    ZxMultilangFieldComponent,
    ZxFormSectionComponent,
    ZxCheckboxGroupComponent,
    ZxButtonControlsComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './author-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorEditPageComponent implements OnInit, OnDestroy {
  readonly form: FormGroup = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    realName: this.fb.nonNullable.control<Record<string, string>>({}),
    country: this.fb.control<EntityRef | null>(null),
    city: this.fb.control<EntityRef | null>(null),
    artCityId: this.fb.nonNullable.control(''),
    wikiLink: this.fb.nonNullable.control(''),
    zxTunesId: this.fb.nonNullable.control(''),
    denyVoting: this.fb.nonNullable.control(false),
    denyComments: this.fb.nonNullable.control(false),
    deny3a: this.fb.nonNullable.control(false),
    displayInMusic: this.fb.nonNullable.control(false),
    displayInGraphics: this.fb.nonNullable.control(false),
  });

  readonly titleMessages = {required: 'author-form.error-title-required'};

  loading = true;
  submitting = false;
  errorMessage = '';
  imageUrl: string | null = null;
  languages: FormLanguage[] = [];
  creating = false;

  private elementId = 0;
  private returnUrl = '/authors';
  private imageFile: File | null = null;
  private removeImage = false;
  private passthrough: Record<string, string> = {};
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
      const letter = this.route.snapshot.paramMap.get('letter');
      this.returnUrl = letter ? `/authors/${encodeURIComponent(letter)}` : '/authors';
    } else {
      this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/author/${this.elementId}`;
    }
    const formData$ = this.creating
      ? this.formData.loadCreate('author', ['country', 'city'])
      : this.formData.load(this.elementId, ['country', 'city']);
    this.subscriptions.add(
      formData$.subscribe({
        next: data => {
          if (data.errorMessage) {
            this.loading = false;
            this.errorMessage = data.errorMessage;
            this.cdr.markForCheck();
            return;
          }
          this.pageMetadata.applyFormTitle(this.route.snapshot, data.entityTitle);
          this.languages = data.languages;
          this.form.patchValue({
            title: String(data.fields['title'] ?? ''),
            realName: data.multilang['realName'] ?? {},
            country: data.refs['country'] ?? null,
            city: data.refs['city'] ?? null,
            artCityId: String(data.fields['artCityId'] ?? ''),
            wikiLink: String(data.fields['wikiLink'] ?? ''),
            zxTunesId: String(data.fields['zxTunesId'] ?? ''),
            denyVoting: !!Number(data.fields['denyVoting']),
            denyComments: !!Number(data.fields['denyComments']),
            deny3a: !!Number(data.fields['deny3a']),
            displayInMusic: !!Number(data.fields['displayInMusic']),
            displayInGraphics: !!Number(data.fields['displayInGraphics']),
          });
          this.imageUrl = data.images['image'] ?? null;
          for (const field of PASSTHROUGH_FIELDS) {
            this.passthrough[field] = String(data.fields[field] ?? '');
          }
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'author-form.error-load';
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
          ...this.passthrough,
          title: value.title,
          country: value.country ? String(value.country.id) : '',
          city: value.city ? String(value.city.id) : '',
          artCityId: value.artCityId,
          wikiLink: value.wikiLink,
          zxTunesId: value.zxTunesId,
          denyVoting: value.denyVoting ? '1' : '',
          denyComments: value.denyComments ? '1' : '',
          deny3a: value.deny3a ? '1' : '',
          displayInMusic: value.displayInMusic ? '1' : '',
          displayInGraphics: value.displayInGraphics ? '1' : '',
        },
        multilang: {realName: value.realName},
        image: {field: 'image', file: this.imageFile, remove: this.removeImage},
      };
    const save$ = this.creating
      ? this.formSave.create('author', payload)
      : this.formSave.save(this.elementId, payload);
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'author-form.error-save';
            this.cdr.markForCheck();
            return;
          }
          this.router.navigateByUrl(`/author/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'author-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
