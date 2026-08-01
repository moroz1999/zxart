import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
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
import {ZxSelectComponent, ZxSelectOption} from '../../shared/ui/zx-select/zx-select.component';
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ZxMultiEntityAutocompleteComponent} from '../../shared/ui/zx-multi-entity-autocomplete/zx-multi-entity-autocomplete.component';
import {FileUploadChange, ZxFileUploadComponent} from '../../shared/ui/zx-file-upload/zx-file-upload.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxCheckboxGroupComponent} from '../../shared/ui/zx-checkbox-group/zx-checkbox-group.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxDeleteEntityButtonComponent} from '../../shared/ui/zx-delete-entity-button/zx-delete-entity-button.component';
import {EntityRef} from '../../shared/models/entity-ref';
import {nonEmptyArray} from '../../shared/utils/non-empty-array.validator';
import {enumDefaultValue} from '../../shared/utils/enum-default';
import {EnumOption, FormParentRef} from '../../shared/models/form-data-response';
import {FileUploadField, FormFieldValue} from '../../shared/models/form-save';
import {ZxFileSelectorComponent} from '../../shared/ui/zx-file-selector/zx-file-selector.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

/**
 * expectedFields preserved unchanged on save until they get dedicated UI.
 * `file`/`trackerFile` are file chunks: omitting them keeps the existing file,
 * so they are not listed here.
 */
const PASSTHROUGH_FIELDS = ['inspired', 'embedCode'];

/** Routed page for `tune/:id/edit`. */
@Component({
  selector: 'zx-tune-edit-page',
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
    ZxFileUploadComponent,
    ZxFileSelectorComponent,
    ZxFormSectionComponent,
    ZxCheckboxGroupComponent,
    ZxButtonControlsComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
    ZxDeleteEntityButtonComponent,
  ],
  templateUrl: './tune-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TuneEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    authors: this.fb.nonNullable.control<EntityRef[]>([], nonEmptyArray),
    /** Batch mode only: the uploaded files, validated like any other required field. */
    batchFiles: this.fb.nonNullable.control<File[]>([]),
    /** Filled from the sound-group enum on load: a tune without one starts on the first group. */
    formatGroup: this.fb.nonNullable.control<string>(''),
    party: this.fb.control<EntityRef | null>(null),
    partyplace: this.fb.nonNullable.control(''),
    compo: this.fb.nonNullable.control<string>(''),
    chipType: this.fb.nonNullable.control<string>(''),
    channelsType: this.fb.nonNullable.control<string>(''),
    frequency: this.fb.nonNullable.control<string>(''),
    intFrequency: this.fb.nonNullable.control<string>(''),
    year: this.fb.nonNullable.control(''),
    game: this.fb.control<EntityRef | null>(null),
    tagsText: this.fb.nonNullable.control(''),
    description: this.fb.nonNullable.control(''),
    denyPlaying: this.fb.nonNullable.control(false),
    denyVoting: this.fb.nonNullable.control(false),
    denyComments: this.fb.nonNullable.control(false),
  });

  readonly titleMessages = {required: 'tune-form.error-title-required'};
  readonly authorsMessages = {required: 'tune-form.error-authors-required'};
  readonly batchFilesMessages = {required: 'tune-form.error-files-required'};

  loading = true;
  submitting = false;
  errorMessage = '';
  /** Batch mode uploads several tunes at once and has no element of its own. */
  batchUpload = false;
  enums: Record<string, EnumOption[]> = {};
  formatGroupOptions: ZxSelectOption[] = [];
  fileNames: Record<string, string> = {};
  readonly emptyFiles = [];

  /** Where the user lands once the tune is deleted. */
  readonly deleteReturnUrl = '/music';

  elementId = 0;
  /** Element the batch upload was started from (author or party). */
  private parentId = 0;
  private returnUrl = '/music';
  private batchFiles: File[] = [];
  private passthrough: Record<string, FormFieldValue> = {};
  private fileChanges: Record<string, FileUploadChange> = {};
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly formData: FormDataApiService,
    private readonly formSave: FormSaveApiService,
    private readonly pageMetadata: PageMetadataService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.batchUpload = this.route.snapshot.data['batchUpload'] === true;
    if (this.batchUpload) {
      // the batch title is optional: each tune falls back to its file name
      this.form.controls['title'].clearValidators();
      this.form.controls['title'].updateValueAndValidity();
      this.form.controls['batchFiles'].setValidators(nonEmptyArray);
      this.form.controls['batchFiles'].updateValueAndValidity();
      this.parentId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/${this.route.snapshot.data['entityPath']}/${this.parentId}`;
    } else {
      this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/tune/${this.elementId}`;
    }
    const formData$ = this.batchUpload
      ? this.formData.loadCreate('musicBatch', ['party', 'game'], undefined, this.parentId)
      : this.formData.load(this.elementId, ['party', 'game']);
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
          this.form.patchValue({
            title: String(data.fields[this.batchUpload ? 'musicTitle' : 'title'] ?? ''),
            authors: data.authorRefs,
            formatGroup: enumDefaultValue(data.enums['formatGroup'], String(data.fields['formatGroup'] ?? '')),
            party: data.refs['party'] ?? null,
            partyplace: String(data.fields['partyplace'] ?? ''),
            compo: String(data.fields['compo'] ?? ''),
            chipType: String(data.fields['chipType'] ?? ''),
            channelsType: String(data.fields['channelsType'] ?? ''),
            frequency: String(data.fields['frequency'] ?? ''),
            intFrequency: String(data.fields['intFrequency'] ?? ''),
            year: String(data.fields['year'] ?? ''),
            game: data.refs['game'] ?? null,
            tagsText: String(data.fields['tagsText'] ?? ''),
            description: String(data.fields['description'] ?? ''),
            denyPlaying: !!Number(data.fields['denyPlaying']),
            denyVoting: !!Number(data.fields['denyVoting']),
            denyComments: !!Number(data.fields['denyComments']),
          });
          this.prefillFromParent(data.parent ?? null);
          this.enums = data.enums;
          this.formatGroupOptions = this.buildFormatGroupOptions(data.enums['formatGroup']);
          this.fileNames = data.files;
          for (const field of PASSTHROUGH_FIELDS) {
            this.passthrough[field] = data.fields[field] ?? '';
          }
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'tune-form.error-load';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  /** Labels the backend's bare sound-group codes from the SPA's own translations. */
  private buildFormatGroupOptions(options: EnumOption[] | undefined): ZxSelectOption[] {
    return (options ?? []).map(option => ({
      value: option.value,
      label: this.translate.instant(`player.formatGroup.${option.value}`),
    }));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCancel(): void {
    this.router.navigateByUrl(this.returnUrl);
  }

  onFileChanged(field: string, change: FileUploadChange): void {
    this.fileChanges[field] = change;
  }

  /**
   * An upload started from an author or party page belongs to that element, so
   * the matching field is filled in for the whole batch.
   */
  private prefillFromParent(parent: FormParentRef | null): void {
    if (!this.batchUpload || parent === null) {
      return;
    }
    const ref: EntityRef = {id: parent.id, title: parent.title};
    if (parent.structureType === 'author' || parent.structureType === 'authorAlias') {
      this.form.controls['authors'].setValue([ref]);
    } else if (parent.structureType === 'party') {
      this.form.controls['party'].setValue(ref);
    }
  }

  onBatchFiles(files: File[]): void {
    this.batchFiles = files;
    this.form.controls['batchFiles'].setValue(files);
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
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
      formatGroup: value.formatGroup,
      party: value.party ? String(value.party.id) : '',
      partyplace: value.partyplace,
      compo: value.compo,
      chipType: value.chipType,
      channelsType: value.channelsType,
      frequency: value.frequency,
      intFrequency: value.intFrequency,
      year: value.year,
      game: value.game ? String(value.game.id) : '',
      tagsText: value.tagsText,
      description: value.description,
      denyVoting: value.denyVoting ? '1' : '',
      denyComments: value.denyComments ? '1' : '',
    };
    const save$ = this.batchUpload
      ? this.formSave.create(
        'musicBatch',
        {fileSelectors: {music: this.batchFiles}, fields: {...commonFields, musicTitle: value.title}},
        undefined,
        this.parentId,
      )
      : this.formSave.save(this.elementId, {
        files,
        fields: {
          ...this.passthrough,
          ...commonFields,
          title: value.title,
          denyPlaying: value.denyPlaying ? '1' : '',
        },
      });
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'tune-form.error-save';
            this.cdr.markForCheck();
            return;
          }
          this.router.navigateByUrl(`/tune/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'tune-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
