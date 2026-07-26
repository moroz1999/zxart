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
import {ZxFormRowComponent} from '../../shared/ui/zx-form/zx-form-row/zx-form-row.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxTextareaComponent} from '../../shared/ui/zx-textarea/zx-textarea.component';
import {ZxSelectComponent, ZxSelectOption} from '../../shared/ui/zx-select/zx-select.component';
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ZxMultiEntityAutocompleteComponent} from '../../shared/ui/zx-multi-entity-autocomplete/zx-multi-entity-autocomplete.component';
import {ZxCategoryTreeSelectComponent} from '../../shared/ui/zx-category-tree-select/zx-category-tree-select.component';
import {ZxMultiSelectFilterComponent} from '../../shared/ui/zx-multi-select-filter/zx-multi-select-filter.component';
import {FileMove, ZxFileSelectorComponent} from '../../shared/ui/zx-file-selector/zx-file-selector.component';
import {ZxTagsFieldComponent} from '../../shared/ui/zx-tags-field/zx-tags-field.component';
import {CategoryTreeNode, EnumOption, FileSelectorItem, FormParentRef} from '../../shared/models/form-data-response';
import {PageMetadataService} from '../../shared/services/page-metadata.service';
import {ScreenshotMoveApiService} from '../../features/prod-details/services/screenshot-move-api.service';
import {ZxMemberRoleEditorComponent} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.component';
import {MemberFields, MemberRoleItem} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.models';
import {ZxCheckboxGroupComponent} from '../../shared/ui/zx-checkbox-group/zx-checkbox-group.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {EntityRef} from '../../shared/models/entity-ref';
import {nonEmptyArray} from '../../shared/utils/non-empty-array.validator';
import {FormFieldValue} from '../../shared/models/form-save';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

/** Fixed legalStatus enum (matches the legacy zxProd.form.tpl dropdown). */
const LEGAL_STATUSES = [
  'unknown', 'allowed', 'allowedzxart', 'forbidden', 'forbiddenzxart',
  'insales', 'mia', 'unreleased', 'recovered', 'donationware',
] as const;

/** expectedFields preserved unchanged on save until they get dedicated UI. */
const PASSTHROUGH_FIELDS: string[] = [];
const EMPTY_MEMBER_FIELDS: MemberFields = {addAuthorRole: {}, addAuthorStartDate: {}, addAuthorEndDate: {}};

/** Routed page for `prod/:id/edit`. */
@Component({
  selector: 'zx-prod-edit-page',
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
    ZxFormRowComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxTextareaComponent,
    ZxSelectComponent,
    ZxEntityAutocompleteComponent,
    ZxMultiEntityAutocompleteComponent,
    ZxCategoryTreeSelectComponent,
    ZxMultiSelectFilterComponent,
    ZxFileSelectorComponent,
    ZxTagsFieldComponent,
    ZxMemberRoleEditorComponent,
    ZxCheckboxGroupComponent,
    ZxButtonControlsComponent,
    ZxFormSectionComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './prod-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProdEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    altTitle: this.fb.nonNullable.control(''),
    externalLink: this.fb.nonNullable.control(''),
    legalStatus: this.fb.nonNullable.control<string>('unknown'),
    party: this.fb.control<EntityRef | null>(null),
    partyplace: this.fb.nonNullable.control(''),
    compo: this.fb.nonNullable.control<string>(''),
    language: this.fb.nonNullable.control<string[]>([]),
    year: this.fb.nonNullable.control(''),
    youtubeId: this.fb.nonNullable.control(''),
    groups: this.fb.nonNullable.control<EntityRef[]>([]),
    publishers: this.fb.nonNullable.control<EntityRef[]>([]),
    compilationItems: this.fb.nonNullable.control<EntityRef[]>([]),
    seriesProds: this.fb.nonNullable.control<EntityRef[]>([]),
    categories: this.fb.nonNullable.control<number[]>([], nonEmptyArray),
    /** Batch mode only: the uploaded software files, validated like any other required field. */
    batchFiles: this.fb.nonNullable.control<File[]>([]),
    htmlDescription: this.fb.nonNullable.control(false),
    description: this.fb.nonNullable.control(''),
    instructions: this.fb.nonNullable.control(''),
    tagsText: this.fb.nonNullable.control(''),
    denyVoting: this.fb.nonNullable.control(false),
    denyComments: this.fb.nonNullable.control(false),
  });

  readonly titleMessages = {required: 'prod-form.error-title-required'};
  readonly categoriesMessages = {required: 'prod-form.error-categories-required'};
  readonly batchFilesMessages = {required: 'prod-form.error-files-required'};
  readonly legalStatusOptions: ZxSelectOption[] = LEGAL_STATUSES.map(value => ({
    value,
    label: this.translate.instant(`prod-form.legal.${value}`),
  }));

  loading = true;
  submitting = false;
  errorMessage = '';
  members: MemberRoleItem[] = [];
  roles: string[] = [];
  categoriesTree: CategoryTreeNode[] = [];
  enums: Record<string, EnumOption[]> = {};
  fileSelectors: Record<string, FileSelectorItem[]> = {};
  readonly emptyFiles: FileSelectorItem[] = [];
  batchUpload = false;

  /** Multi-file selectors shown on the prod form: property → label key. */
  readonly fileSelectorDefs = [
    {prop: 'connectedFile', labelKey: 'prod-form.screenshots'},
    {prop: 'inlayFilesSelector', labelKey: 'prod-form.inlays'},
    {prop: 'mapFilesSelector', labelKey: 'prod-form.maps'},
    {prop: 'rzx', labelKey: 'prod-form.rzx'},
  ];
  readonly batchFileSelectorDefs = [
    {prop: 'file', labelKey: 'prod-form.files'},
    {prop: 'connectedFile', labelKey: 'prod-form.screenshots'},
    {prop: 'mapFilesSelector', labelKey: 'prod-form.maps'},
  ];

  private elementId = 0;
  /** Element the batch upload was started from (author, group, party or category). */
  private parentId = 0;
  private returnUrl = '/prods';
  private memberFields: MemberFields = EMPTY_MEMBER_FIELDS;
  private passthrough: Record<string, FormFieldValue> = {};
  private selectorFiles: Record<string, File[]> = {};
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly translate: TranslateService,
    private readonly formData: FormDataApiService,
    private readonly formSave: FormSaveApiService,
    private readonly screenshotMove: ScreenshotMoveApiService,
    private readonly pageMetadata: PageMetadataService,
  ) {}

  ngOnInit(): void {
    this.batchUpload = this.route.snapshot.data['batchUpload'] === true;
    if (this.batchUpload) {
      this.form.controls['title'].clearValidators();
      this.form.controls['title'].updateValueAndValidity();
      this.form.controls['batchFiles'].setValidators(nonEmptyArray);
      this.form.controls['batchFiles'].updateValueAndValidity();
      // started from an entity page (author, group, party) or from a browsed category
      const entityPath = this.route.snapshot.data['entityPath'] as string | undefined;
      this.parentId = Number(this.route.snapshot.paramMap.get('id'))
        || Number(this.route.snapshot.queryParamMap.get('cat'))
        || 0;
      this.returnUrl = entityPath && this.parentId ? `/${entityPath}/${this.parentId}` : '/prods';
    } else {
      this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/prod/${this.elementId}`;
    }
    const formData$ = this.batchUpload
      ? this.formData.loadCreate('prodBatch', ['party'], undefined, this.parentId || undefined)
      : this.formData.load(this.elementId, ['party']);
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
            title: String(data.fields[this.batchUpload ? 'prodTitle' : 'title'] ?? ''),
            altTitle: String(data.fields[this.batchUpload ? 'prodAltTitle' : 'altTitle'] ?? ''),
            externalLink: String(data.fields['externalLink'] ?? ''),
            legalStatus: String(data.fields['legalStatus'] ?? 'unknown') || 'unknown',
            party: data.refs['party'] ?? null,
            partyplace: String(data.fields['partyplace'] ?? ''),
            compo: String(data.fields['compo'] ?? ''),
            language: Array.isArray(data.fields['language']) ? (data.fields['language'] as string[]) : [],
            year: String(data.fields['year'] ?? ''),
            youtubeId: String(data.fields['youtubeId'] ?? ''),
            groups: data.multiRefs['groups'] ?? [],
            publishers: data.multiRefs['publishers'] ?? [],
            compilationItems: data.multiRefs['compilationItems'] ?? [],
            seriesProds: data.multiRefs['seriesProds'] ?? [],
            categories: data.categoriesTree.filter(node => node.selected).map(node => node.id),
            htmlDescription: !!Number(data.fields['htmlDescription']),
            description: String(data.fields['description'] ?? ''),
            instructions: String(data.fields['instructions'] ?? ''),
            tagsText: String(data.fields['tagsText'] ?? ''),
            denyVoting: !!Number(data.fields['denyVoting']),
            denyComments: !!Number(data.fields['denyComments']),
          });
          this.members = data.members;
          this.roles = data.roles;
          this.categoriesTree = data.categoriesTree;
          this.prefillFromParent(data.parent ?? null);
          this.enums = data.enums;
          this.fileSelectors = data.fileSelectors;
          for (const field of PASSTHROUGH_FIELDS) {
            this.passthrough[field] = data.fields[field] ?? '';
          }
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'prod-form.error-load';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onMemberFields(fields: MemberFields): void {
    this.memberFields = fields;
  }

  onRemoveMember(authorId: number): void {
    if (this.batchUpload) {
      return;
    }
    this.subscriptions.add(this.formSave.deleteMember(this.elementId, authorId).subscribe());
  }

  /**
   * An upload started from an author, group, party or category page belongs to
   * that element, so the matching field is filled in for the whole batch. The
   * author becomes a member, because that is how a production carries authorship.
   */
  private prefillFromParent(parent: FormParentRef | null): void {
    if (!this.batchUpload || parent === null) {
      return;
    }
    const ref: EntityRef = {id: parent.id, title: parent.title};
    switch (parent.structureType) {
      case 'author':
      case 'authorAlias':
        this.members = [{id: parent.id, title: parent.title, startDate: '', endDate: '', roles: []}];
        break;
      case 'group':
      case 'groupAlias':
        this.form.controls['groups'].setValue([ref]);
        break;
      case 'party':
        this.form.controls['party'].setValue(ref);
        break;
      case 'zxProdCategory':
        this.form.controls['categories'].setValue([parent.id]);
        break;
    }
  }

  onSelectorFiles(prop: string, files: File[]): void {
    this.selectorFiles[prop] = files;
    if (prop === 'file') {
      this.form.controls['batchFiles'].setValue(files);
    }
  }

  onSelectorRemove(fileId: number): void {
    this.subscriptions.add(this.formSave.deleteFileElement(fileId).subscribe());
  }

  /** Live-reorder the prod screenshots (connectedFile) via the move endpoint. */
  onSelectorMove(prop: string, move: FileMove): void {
    if (prop !== 'connectedFile') {
      return;
    }
    this.subscriptions.add(
      this.screenshotMove.move(this.elementId, move.fileId, move.direction).subscribe(files => {
        if (files !== null) {
          this.fileSelectors = {
            ...this.fileSelectors,
            [prop]: files.map(file => ({
              id: file.id,
              title: file.title,
              isImage: file.isImage,
              imageUrl: file.imageUrl,
            })),
          };
          this.cdr.markForCheck();
        }
      }),
    );
  }

  onCancel(): void {
    this.router.navigateByUrl(this.returnUrl);
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    const commonFields = {
      externalLink: value.externalLink,
      legalStatus: value.legalStatus,
      party: value.party ? String(value.party.id) : '',
      partyplace: value.partyplace,
      compo: value.compo,
      language: value.language,
      year: value.year,
      youtubeId: value.youtubeId,
      groups: value.groups.map((ref: EntityRef) => String(ref.id)),
      publishers: value.publishers.map((ref: EntityRef) => String(ref.id)),
      categories: value.categories.map((id: number) => String(id)),
      description: value.description,
      instructions: value.instructions,
      tagsText: value.tagsText,
      denyVoting: value.denyVoting ? '1' : '',
      denyComments: value.denyComments ? '1' : '',
      htmlDescription: value.htmlDescription ? '1' : '',
      addAuthorRole: this.memberFields.addAuthorRole,
    };
    const save$ = this.batchUpload
      ? this.formSave.create(
        'prodBatch',
        {
          fileSelectors: this.selectorFiles,
          fields: {
            ...commonFields,
            prodTitle: value.title,
            prodAltTitle: value.altTitle,
          },
        },
        undefined,
        this.parentId || undefined,
      )
      : this.formSave.save(this.elementId, {
        fileSelectors: this.selectorFiles,
        fields: {
          ...this.passthrough,
          ...commonFields,
          title: value.title,
          altTitle: value.altTitle,
          compilationItems: value.compilationItems.map((ref: EntityRef) => String(ref.id)),
          seriesProds: value.seriesProds.map((ref: EntityRef) => String(ref.id)),
        },
      });
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'prod-form.error-save';
            this.cdr.markForCheck();
            return;
          }
          this.router.navigateByUrl(`/prod/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'prod-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
