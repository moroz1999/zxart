import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {PageMetadataService} from '../../shared/services/page-metadata.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxCheckboxFieldComponent} from '../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxControlErrorsComponent} from '../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormRowComponent} from '../../shared/ui/zx-form/zx-form-row/zx-form-row.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxTagsFieldComponent} from '../../shared/ui/zx-tags-field/zx-tags-field.component';
import {ZxTextareaComponent} from '../../shared/ui/zx-textarea/zx-textarea.component';
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ZxMultiEntityAutocompleteComponent} from '../../shared/ui/zx-multi-entity-autocomplete/zx-multi-entity-autocomplete.component';
import {ImageUploadChange, ZxImageUploadComponent} from '../../shared/ui/zx-image-upload/zx-image-upload.component';
import {ZxFileUploadComponent} from '../../shared/ui/zx-file-upload/zx-file-upload.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxCheckboxGroupComponent} from '../../shared/ui/zx-checkbox-group/zx-checkbox-group.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxSelectComponent, ZxSelectOption} from '../../shared/ui/zx-select/zx-select.component';
import {EntityRef} from '../../shared/models/entity-ref';
import {nonEmptyArray} from '../../shared/utils/non-empty-array.validator';
import {EnumOption} from '../../shared/models/form-data-response';
import {FileUploadField, FormFieldValue} from '../../shared/services/form-save-api.service';
import {ZxFileSelectorComponent} from '../../shared/ui/zx-file-selector/zx-file-selector.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

/**
 * expectedFields preserved unchanged on save until they get dedicated UI.
 * Backend-driven enums (border/compo/type/rotation/palette) are plain text
 * stored on the element, so passthrough keeps them. Image/file chunks (image,
 * inspired, inspired2, sequence, exeFile) keep the existing file when omitted.
 */
const PASSTHROUGH_FIELDS: string[] = [];

/** Fixed border-colour enum (matches the legacy zxPicture.form.tpl dropdown). */
const BORDER_OPTIONS: ZxSelectOption[] = [
  {value: '0', label: 'Black'},
  {value: '1', label: 'Blue'},
  {value: '2', label: 'Red'},
  {value: '3', label: 'Magenta'},
  {value: '4', label: 'Green'},
  {value: '5', label: 'Cyan'},
  {value: '6', label: 'Yellow'},
  {value: '7', label: 'White'},
];

/** Fixed rotation enum (degrees). */
const ROTATION_OPTIONS: ZxSelectOption[] = [
  {value: '0', label: '0'},
  {value: '90', label: '90'},
  {value: '180', label: '180'},
  {value: '270', label: '270'},
];

/** Routed page for `picture/:id/edit` and for batch upload from an author or party. */
@Component({
  selector: 'zx-picture-edit-page',
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
    ZxFormRowComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxTagsFieldComponent,
    ZxTextareaComponent,
    ZxSelectComponent,
    ZxEntityAutocompleteComponent,
    ZxMultiEntityAutocompleteComponent,
    ZxImageUploadComponent,
    ZxFileUploadComponent,
    ZxFileSelectorComponent,
    ZxFormSectionComponent,
    ZxCheckboxGroupComponent,
    ZxButtonControlsComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './picture-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureEditPageComponent implements OnInit, OnDestroy {
  readonly form: FormGroup = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    authors: this.fb.nonNullable.control<EntityRef[]>([], nonEmptyArray),
    originalAuthors: this.fb.nonNullable.control<EntityRef[]>([]),
    party: this.fb.control<EntityRef | null>(null),
    partyplace: this.fb.nonNullable.control(''),
    compo: this.fb.nonNullable.control<string>(''),
    type: this.fb.nonNullable.control<string>(''),
    border: this.fb.nonNullable.control<string>('0'),
    rotation: this.fb.nonNullable.control<string>('0'),
    palette: this.fb.nonNullable.control<string>(''),
    year: this.fb.nonNullable.control(''),
    game: this.fb.control<EntityRef | null>(null),
    tagsText: this.fb.nonNullable.control(''),
    description: this.fb.nonNullable.control(''),
    denyVoting: this.fb.nonNullable.control(false),
    denyComments: this.fb.nonNullable.control(false),
  });

  readonly titleMessages = {required: 'picture-form.error-title-required'};
  readonly authorsMessages = {required: 'picture-form.error-authors-required'};
  readonly borderOptions = BORDER_OPTIONS;
  readonly rotationOptions = ROTATION_OPTIONS;

  /** Image fields shown with zx-image-upload (all imageDataChunk on zxPicture). */
  readonly imageFields: readonly string[] = ['image', 'inspired', 'inspired2', 'sequence'];

  loading = true;
  submitting = false;
  errorMessage = '';
  /** Batch mode uploads several pictures at once and has no element of its own. */
  batchUpload = false;
  enums: Record<string, EnumOption[]> = {};
  imageUrls: Record<string, string> = {};
  fileNames: Record<string, string> = {};
  readonly emptyFiles = [];
  readonly emptyImageFields: readonly string[] = [];

  private elementId = 0;
  /** Element the batch upload was started from (author or party). */
  private parentId = 0;
  private returnUrl = '/pictures';
  private batchFiles: File[] = [];
  private passthrough: Record<string, FormFieldValue> = {};
  /** Pending file change per image field (replace and/or remove). */
  private fileChanges: Record<string, ImageUploadChange> = {};
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
    this.batchUpload = this.route.snapshot.data['batchUpload'] === true;
    if (this.batchUpload) {
      // the batch title is optional: each picture falls back to its file name
      this.form.controls['title'].clearValidators();
      this.form.controls['title'].updateValueAndValidity();
      this.parentId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/${this.route.snapshot.data['entityPath']}/${this.parentId}`;
    } else {
      this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/picture/${this.elementId}`;
    }
    const formData$ = this.batchUpload
      ? this.formData.loadCreate('pictureBatch', ['party', 'game'], undefined, this.parentId)
      : this.formData.load(this.elementId, ['party', 'game']);
    this.subscriptions.add(
      formData$.subscribe({
        next: data => {
          this.pageMetadata.applyFormTitle(this.route.snapshot, data.entityTitle);
          this.form.patchValue({
            title: String(data.fields[this.batchUpload ? 'pictureTitle' : 'title'] ?? ''),
            authors: data.authorRefs,
            originalAuthors: data.originalAuthorRefs,
            party: data.refs['party'] ?? null,
            partyplace: String(data.fields['partyplace'] ?? ''),
            compo: String(data.fields['compo'] ?? ''),
            type: String(data.fields['type'] ?? ''),
            border: String(data.fields['border'] ?? '0') || '0',
            rotation: String(data.fields['rotation'] ?? '0') || '0',
            palette: String(data.fields['palette'] ?? ''),
            year: String(data.fields['year'] ?? ''),
            game: data.refs['game'] ?? null,
            tagsText: String(data.fields['tagsText'] ?? ''),
            description: String(data.fields['description'] ?? ''),
            denyVoting: !!Number(data.fields['denyVoting']),
            denyComments: !!Number(data.fields['denyComments']),
          });
          this.enums = data.enums;
          this.imageUrls = data.images;
          this.fileNames = data.files;
          for (const field of PASSTHROUGH_FIELDS) {
            this.passthrough[field] = data.fields[field] ?? '';
          }
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'picture-form.error-load';
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

  onImageChanged(field: string, change: ImageUploadChange): void {
    this.fileChanges[field] = change;
  }

  onBatchFiles(files: File[]): void {
    this.batchFiles = files;
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    if (this.batchUpload && this.batchFiles.length === 0) {
      this.errorMessage = 'picture-form.error-files-required';
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    const files: FileUploadField[] = Object.entries(this.fileChanges).map(([field, change]) => ({
      field,
      file: change.file,
      remove: change.removed,
    }));
    const commonFields = {
      author: value.authors.map((ref: EntityRef) => String(ref.id)),
      originalAuthor: value.originalAuthors.map((ref: EntityRef) => String(ref.id)),
      party: value.party ? String(value.party.id) : '',
      partyplace: value.partyplace,
      compo: value.compo,
      type: value.type,
      border: value.border,
      rotation: value.rotation,
      palette: value.palette,
      year: value.year,
      game: value.game ? String(value.game.id) : '',
      tagsText: value.tagsText,
      description: value.description,
      denyVoting: value.denyVoting ? '1' : '',
      denyComments: value.denyComments ? '1' : '',
    };
    const save$ = this.batchUpload
      ? this.formSave.create(
        'pictureBatch',
        {fileSelectors: {image: this.batchFiles}, fields: {...commonFields, pictureTitle: value.title}},
        undefined,
        this.parentId,
      )
      : this.formSave.save(this.elementId, {
        files,
        fields: {...this.passthrough, ...commonFields, title: value.title},
      });
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          this.router.navigateByUrl(`/picture/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'picture-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
