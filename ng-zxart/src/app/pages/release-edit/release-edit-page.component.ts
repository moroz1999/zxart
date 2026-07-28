import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
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
import {ZxMemberRoleEditorComponent} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.component';
import {MemberFields, MemberRoleItem} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.models';
import {ZxFileSelectorComponent} from '../../shared/ui/zx-file-selector/zx-file-selector.component';
import {ZxSelectComponent} from '../../shared/ui/zx-select/zx-select.component';
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ZxMultiEntityAutocompleteComponent} from '../../shared/ui/zx-multi-entity-autocomplete/zx-multi-entity-autocomplete.component';
import {FileUploadChange, ZxFileUploadComponent} from '../../shared/ui/zx-file-upload/zx-file-upload.component';
import {EntityRef} from '../../shared/models/entity-ref';
import {EnumOption, FileSelectorItem} from '../../shared/models/form-data-response';
import {PageMetadataService} from '../../shared/services/page-metadata.service';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxCheckboxGroupComponent} from '../../shared/ui/zx-checkbox-group/zx-checkbox-group.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxMultiSelectFilterComponent} from '../../shared/ui/zx-multi-select-filter/zx-multi-select-filter.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {FileUploadField} from '../../shared/models/form-save';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

const EMPTY_MEMBER_FIELDS: MemberFields = {addAuthorRole: {}, addAuthorStartDate: {}, addAuthorEndDate: {}};

/** Routed page for `release/:id/edit`. */
@Component({
  selector: 'zx-release-edit-page',
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
    ZxMemberRoleEditorComponent,
    ZxFileSelectorComponent,
    ZxSelectComponent,
    ZxEntityAutocompleteComponent,
    ZxMultiEntityAutocompleteComponent,
    ZxFileUploadComponent,
    ZxFormSectionComponent,
    ZxCheckboxGroupComponent,
    ZxButtonControlsComponent,
    ZxMultiSelectFilterComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './release-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReleaseEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    prod: this.fb.control<EntityRef | null>(null, Validators.required),
    version: this.fb.nonNullable.control(''),
    year: this.fb.nonNullable.control(''),
    releaseType: this.fb.nonNullable.control<string>(''),
    releaseFormat: this.fb.nonNullable.control<string[]>([]),
    language: this.fb.nonNullable.control<string[]>([]),
    hardwareRequired: this.fb.nonNullable.control<string[]>([]),
    publishers: this.fb.nonNullable.control<EntityRef[]>([]),
    description: this.fb.nonNullable.control(''),
    denyVoting: this.fb.nonNullable.control(false),
    denyComments: this.fb.nonNullable.control(false),
  });

  readonly titleMessages = {required: 'release-form.error-title-required'};
  readonly prodMessages = {required: 'release-form.error-prod-required'};

  loading = true;
  submitting = false;
  errorMessage = '';
  members: MemberRoleItem[] = [];
  roles: string[] = [];
  enums: Record<string, EnumOption[]> = {};
  /** Language codes come from the backend; their names are ours (`language.<code>`). */
  languageOptions: EnumOption[] = [];
  fileNames: Record<string, string> = {};
  fileSelectors: Record<string, FileSelectorItem[]> = {};

  /** Multi-file selectors shown on the release form: property → label key. */
  readonly fileSelectorDefs = [
    {prop: 'screenshotsSelector', labelKey: 'release-form.screenshots'},
    {prop: 'inlayFilesSelector', labelKey: 'release-form.inlays'},
    {prop: 'infoFilesSelector', labelKey: 'release-form.info-files'},
    {prop: 'adFilesSelector', labelKey: 'release-form.ad-files'},
  ];

  /** Creation mode: a new release of the production the form was opened from. */
  creating = false;

  private elementId = 0;
  /** The production the new release belongs to. */
  private prodId = 0;
  private returnUrl = '/prods';
  private memberFields: MemberFields = EMPTY_MEMBER_FIELDS;
  private selectorFiles: Record<string, File[]> = {};
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
    this.creating = this.route.snapshot.data['create'] === true;
    if (this.creating) {
      this.prodId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/prod/${this.prodId}`;
    } else {
      this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/release/${this.elementId}`;
    }
    const formData$ = this.creating
      ? this.formData.loadCreate('release', [], undefined, this.prodId)
      : this.formData.load(this.elementId);
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
            title: String(data.fields['title'] ?? ''),
            prod: data.multiRefs['zxProd']?.[0] ?? null,
            version: String(data.fields['version'] ?? ''),
            year: String(data.fields['year'] ?? ''),
            releaseType: String(data.fields['releaseType'] ?? ''),
            releaseFormat: Array.isArray(data.fields['releaseFormat']) ? (data.fields['releaseFormat'] as string[]) : [],
            language: Array.isArray(data.fields['language']) ? (data.fields['language'] as string[]) : [],
            hardwareRequired: Array.isArray(data.fields['hardwareRequired']) ? (data.fields['hardwareRequired'] as string[]) : [],
            publishers: data.multiRefs['publishers'] ?? [],
            description: String(data.fields['description'] ?? ''),
            denyVoting: !!Number(data.fields['denyVoting']),
            denyComments: !!Number(data.fields['denyComments']),
          });
          this.members = data.members;
          this.roles = data.roles;
          this.enums = data.enums;
          this.languageOptions = this.buildLanguageOptions(data.enums['language']);
          this.fileNames = data.files;
          this.fileSelectors = data.fileSelectors;
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'release-form.error-load';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  /** Labels the backend's bare language codes from the SPA's own translations. */
  private buildLanguageOptions(options: EnumOption[] | undefined): EnumOption[] {
    return (options ?? []).map(option => ({
      value: option.value,
      label: this.translate.instant(`language.${option.value}`),
    }));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCancel(): void {
    this.router.navigateByUrl(this.returnUrl);
  }

  onMemberFields(fields: MemberFields): void {
    this.memberFields = fields;
  }

  onRemoveMember(authorId: number): void {
    this.subscriptions.add(this.formSave.deleteMember(this.elementId, authorId).subscribe());
  }

  onSelectorFiles(prop: string, files: File[]): void {
    this.selectorFiles[prop] = files;
  }

  onSelectorRemove(fileId: number): void {
    this.subscriptions.add(this.formSave.deleteFileElement(fileId).subscribe());
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
    const payload = {
      fileSelectors: this.selectorFiles,
      files,
      fields: {
        title: value.title,
        zxProd: value.prod ? String(value.prod.id) : String(this.prodId || ''),
        version: value.version,
        year: value.year,
        releaseType: value.releaseType,
        releaseFormat: value.releaseFormat,
        language: value.language,
        hardwareRequired: value.hardwareRequired,
        publishers: value.publishers.map((ref: EntityRef) => String(ref.id)),
        description: value.description,
        denyVoting: value.denyVoting ? '1' : '',
        denyComments: value.denyComments ? '1' : '',
        addAuthorRole: this.memberFields.addAuthorRole,
      },
    };
    const save$ = this.creating
      ? this.formSave.create('release', payload, undefined, this.prodId)
      : this.formSave.save(this.elementId, payload);
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'release-form.error-save';
            this.cdr.markForCheck();
            return;
          }
          this.router.navigateByUrl(`/release/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'release-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
