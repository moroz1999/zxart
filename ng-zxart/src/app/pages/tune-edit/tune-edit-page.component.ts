import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
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
import {ZxTextareaComponent} from '../../shared/ui/zx-textarea/zx-textarea.component';
import {ZxSelectComponent, ZxSelectOption} from '../../shared/ui/zx-select/zx-select.component';
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ZxMultiEntityAutocompleteComponent} from '../../shared/ui/zx-multi-entity-autocomplete/zx-multi-entity-autocomplete.component';
import {FileUploadChange, ZxFileUploadComponent} from '../../shared/ui/zx-file-upload/zx-file-upload.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {EntityRef} from '../../shared/models/entity-ref';
import {EnumOption} from '../../shared/models/form-data-response';
import {FileUploadField, FormFieldValue} from '../../shared/services/form-save-api.service';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

/** Fixed formatGroup enum (matches the legacy zxMusic.form.tpl dropdown). */
const FORMAT_GROUPS: ReadonlyArray<{value: string; label: string}> = [
  {value: 'ay', label: 'AY/YM'},
  {value: 'beeper', label: 'Beeper'},
  {value: 'digitalbeeper', label: 'Digital Beeper'},
  {value: 'beeperdigitalbeeper', label: 'Beeper + Digital Beeper'},
  {value: 'digitalay', label: 'Digital AY, Covox, SD'},
  {value: 'ts', label: 'Turbo Sound'},
  {value: 'fm', label: 'FM'},
  {value: 'tsfm', label: 'Turbo Sound FM'},
  {value: 'aybeeper', label: 'AY/YM + Beeper'},
  {value: 'aydigitalay', label: 'AY/YM + Digital AY'},
  {value: 'aycovox', label: 'AY/YM + Covox'},
  {value: 'saa', label: 'SAA'},
];

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
    ZxFormDirective,
    ZxInputComponent,
    ZxTextareaComponent,
    ZxSelectComponent,
    ZxEntityAutocompleteComponent,
    ZxMultiEntityAutocompleteComponent,
    ZxFileUploadComponent,
    ZxStackComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './tune-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TuneEditPageComponent implements OnInit, OnDestroy {
  readonly form: FormGroup = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    authors: this.fb.nonNullable.control<EntityRef[]>([]),
    formatGroup: this.fb.nonNullable.control<string>('ay'),
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
  readonly formatGroupOptions: ZxSelectOption[] = FORMAT_GROUPS.map(g => ({value: g.value, label: g.label}));

  loading = true;
  submitting = false;
  errorMessage = '';
  enums: Record<string, EnumOption[]> = {};
  fileNames: Record<string, string> = {};

  private elementId = 0;
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
  ) {}

  ngOnInit(): void {
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.subscriptions.add(
      this.formData.load(this.elementId, ['party', 'game']).subscribe({
        next: data => {
          this.form.patchValue({
            title: String(data.fields['title'] ?? ''),
            authors: data.authorRefs,
            formatGroup: String(data.fields['formatGroup'] ?? '') || 'ay',
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
          this.enums = data.enums;
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

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onFileChanged(field: string, change: FileUploadChange): void {
    this.fileChanges[field] = change;
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
    this.subscriptions.add(
      this.formSave.save(this.elementId, {
        files,
        fields: {
          ...this.passthrough,
          title: value.title,
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
          denyPlaying: value.denyPlaying ? '1' : '',
          denyVoting: value.denyVoting ? '1' : '',
          denyComments: value.denyComments ? '1' : '',
        },
      }).subscribe({
        next: result => {
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
