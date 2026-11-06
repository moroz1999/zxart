import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable, Subscription} from 'rxjs';
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
import {ZxImportOriginsEditorComponent} from '../../shared/ui/zx-import-origins-editor/zx-import-origins-editor.component';
import {ImportOriginFields, ImportOriginItem} from '../../shared/ui/zx-import-origins-editor/zx-import-origins-editor.models';
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
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {MultiSelectGroup} from '../../shared/ui/zx-multi-select-filter/zx-multi-select-filter.models';
import {buildHardwareGroups} from '../../shared/utils/hardware-groups';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxDeleteEntityButtonComponent} from '../../shared/ui/zx-delete-entity-button/zx-delete-entity-button.component';
import {ReleaseDataApiService} from '../../shared/api/release-data-api.service';
import {EntityChangeResult} from '../../shared/models/entity-change-result';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {EntityFields, FilesByKind} from '../../shared/models/entity-save';
import {authorshipInputs, importOriginInputs} from '../../shared/utils/entity-save-inputs';
import {enumDefaultValue} from '../../shared/utils/enum-default';

/** The file list each selector feeds, by the selector's property. */
const FILE_KINDS: Readonly<Record<string, string>> = {
  screenshotsSelector: 'screenshots',
  inlayFilesSelector: 'inlays',
  infoFilesSelector: 'info',
  adFilesSelector: 'ads',
};
const EMPTY_MEMBER_FIELDS: MemberFields = {roles: {}, startDates: {}, endDates: {}};

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
    ZxImportOriginsEditorComponent,
    ZxFileSelectorComponent,
    ZxSelectComponent,
    ZxEntityAutocompleteComponent,
    ZxMultiEntityAutocompleteComponent,
    ZxFileUploadComponent,
    ZxFormSectionComponent,
    ZxCheckboxGroupComponent,
    ZxButtonControlsComponent,
    ZxMultiSelectFilterComponent,
    TextDirective,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
    ZxDeleteEntityButtonComponent,
  ],
  templateUrl: './release-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReleaseEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    title: this.fb.nonNullable.control(''),
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

  readonly prodMessages = {required: 'release-form.error-prod-required'};

  loading = true;
  submitting = false;
  errorMessage = '';
  members: MemberRoleItem[] = [];
  roles: string[] = [];
  enums: Record<string, EnumOption[]> = {};
  importOrigins: ImportOriginItem[] = [];
  /** Release format codes come from the backend; their names are ours (`release-format.<code>`). */
  releaseFormatOptions: EnumOption[] = [];
  /** Language codes come from the backend; their names are ours (`language.<code>`). */
  languageOptions: EnumOption[] = [];
  /** Hardware arrives labelled and grouped from the backend catalog. */
  hardwareGroups: MultiSelectGroup[] = [];
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

  /** Where the user lands once the release is deleted; the parent production. */
  deleteReturnUrl = '/prods';
  readonly deleteRequest = (id: number): Observable<EntityChangeResult> => this.releaseDataApi.delete(id);

  elementId = 0;
  /** The production the new release belongs to. */
  private prodId = 0;
  private returnUrl = '/prods';
  private memberFields: MemberFields = EMPTY_MEMBER_FIELDS;
  private importOriginFields: ImportOriginFields = {};
  /** Creation mode: one release is created per picked file. */
  private newReleaseFiles: File[] = [];
  private selectorFiles: Record<string, File[]> = {};
  private fileChanges: Record<string, FileUploadChange> = {};
  private releaseFormatCodes: EnumOption[] = [];
  private languageCodes: EnumOption[] = [];
  private hardwareCodes: EnumOption[] = [];
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly releaseDataApi: ReleaseDataApiService,
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly formData: FormDataApiService,
    private readonly pageMetadata: PageMetadataService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(this.translate.onLangChange.subscribe(() => {
      this.buildClientLabelOptions();
      this.cdr.markForCheck();
    }));
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
          const parentProd = data.multiRefs['zxProd']?.[0] ?? null;
          if (parentProd) {
            this.deleteReturnUrl = `/prod/${parentProd.id}`;
          }
          this.form.patchValue({
            title: String(data.fields['title'] ?? ''),
            prod: parentProd,
            version: String(data.fields['version'] ?? ''),
            year: String(data.fields['year'] ?? ''),
            releaseType: enumDefaultValue(data.enums['releaseType'], String(data.fields['releaseType'] ?? '')),
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
          this.importOrigins = data.importOrigins;
          this.releaseFormatCodes = data.enums['releaseFormat'] ?? [];
          this.languageCodes = data.enums['language'] ?? [];
          this.hardwareCodes = data.enums['hardwareRequired'] ?? [];
          this.buildClientLabelOptions();
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

  /** Labels the backend's bare release format and language codes; hardware arrives already labelled. */
  private buildClientLabelOptions(): void {
    this.releaseFormatOptions = this.releaseFormatCodes.map(option => ({
      value: option.value,
      label: this.translate.instant(`release-format.${option.value}`),
    }));
    this.languageOptions = this.languageCodes.map(option => ({
      value: option.value,
      label: this.translate.instant(`language.${option.value}`),
    }));
    this.hardwareGroups = buildHardwareGroups(this.hardwareCodes, this.translate);
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCancel(): void {
    this.router.navigateByUrl(this.returnUrl);
  }

  onImportOriginFields(fields: ImportOriginFields): void {
    this.importOriginFields = fields;
  }

  onMemberFields(fields: MemberFields): void {
    this.memberFields = fields;
  }

  onRemoveMember(authorId: number): void {
    this.subscriptions.add(this.releaseDataApi.deleteMember(this.elementId, authorId).subscribe({
      error: () => this.showError('form.member-remove-failed'),
    }));
  }

  onSelectorFiles(prop: string, files: File[]): void {
    this.selectorFiles[prop] = files;
  }

  onSelectorRemove(fileId: number): void {
    this.subscriptions.add(this.releaseDataApi.deleteFile(this.elementId, fileId).subscribe({
      error: () => this.showError('form.file-remove-failed'),
    }));
  }

  onFileChanged(field: string, change: FileUploadChange): void {
    this.fileChanges[field] = change;
  }

  onNewReleaseFiles(files: File[]): void {
    this.newReleaseFiles = files;
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    const fields: EntityFields = {
      title: value.title,
      prodId: value.prod?.id ?? this.prodId,
      version: value.version,
      year: Number(value.year) || 0,
      releaseType: value.releaseType,
      releaseFormats: value.releaseFormat,
      languages: value.language,
      hardware: value.hardwareRequired,
      publisherIds: value.publishers.map((ref: EntityRef) => ref.id),
      description: value.description,
      denyVoting: value.denyVoting,
      denyComments: value.denyComments,
    };
    const authors = authorshipInputs(this.memberFields);
    if (this.creating) {
      this.subscriptions.add(
        this.releaseDataApi.createFromFiles(
          {fields, authors},
          {releaseFiles: this.newReleaseFiles, files: this.filesByKind()},
        ).subscribe({
          next: result => {
            // several releases at once have no single page to land on; their
            // production lists them all
            this.router.navigateByUrl(result.ids.length === 1 ? `/release/${result.ids[0]}` : this.returnUrl);
          },
          error: (err: HttpErrorResponse) => this.saveFailed(err),
        }),
      );
      return;
    }
    const fileChange = this.fileChanges['file'];
    this.subscriptions.add(
      this.releaseDataApi.update(
        this.elementId,
        {
          fields,
          authors,
          importOrigins: importOriginInputs(this.importOriginFields),
          removeFile: fileChange?.removed ?? false,
        },
        {file: fileChange?.file ?? null, files: this.filesByKind()},
      ).subscribe({
        next: result => {
          this.router.navigateByUrl(`/release/${result.id}`);
        },
        error: (err: HttpErrorResponse) => this.saveFailed(err),
      }),
    );
  }

  private filesByKind(): FilesByKind {
    const files: FilesByKind = {};
    for (const [prop, kind] of Object.entries(FILE_KINDS)) {
      files[kind] = this.selectorFiles[prop] ?? [];
    }
    return files;
  }

  private saveFailed(err: HttpErrorResponse): void {
    this.submitting = false;
    this.errorMessage = err.error?.errorMessage ?? 'release-form.error-save';
    this.cdr.markForCheck();
  }

  private showError(messageKey: string): void {
    this.errorMessage = messageKey;
    this.cdr.markForCheck();
  }
}
