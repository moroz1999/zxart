import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {EntityRef} from '../../shared/models/entity-ref';
import {EnumOption} from '../../shared/models/form-data-response';
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
import {ZxImportOriginsEditorComponent} from '../../shared/ui/zx-import-origins-editor/zx-import-origins-editor.component';
import {ImportOriginFields, ImportOriginItem} from '../../shared/ui/zx-import-origins-editor/zx-import-origins-editor.models';
import {ZxMemberRoleEditorComponent} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.component';
import {MemberFields, MemberRoleItem} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.models';
import {ZxTabsComponent} from '../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../shared/ui/zx-tabs/zx-tab.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxDeleteEntityButtonComponent} from '../../shared/ui/zx-delete-entity-button/zx-delete-entity-button.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';
import {PageMetadataService} from '../../shared/services/page-metadata.service';
import {AuthorAliasFormApiService} from '../../features/author-details/services/author-alias-form-api.service';

const EMPTY_MEMBER_FIELDS: MemberFields = {roles: {}, startDates: {}, endDates: {}};

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
    ZxMemberRoleEditorComponent,
    ZxImportOriginsEditorComponent,
    ZxTabsComponent,
    ZxTabComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
    ZxDeleteEntityButtonComponent,
  ],
  templateUrl: './author-alias-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorAliasEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
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
  /** Authorship of the alias itself, editable only once the alias exists. */
  groups: MemberRoleItem[] = [];
  groupRoles: string[] = [];
  prods: MemberRoleItem[] = [];
  prodRoles: string[] = [];
  importOrigins: ImportOriginItem[] = [];
  importOriginOptions: EnumOption[] = [];
  prodsCount = 0;
  activeTab = 0;

  /** Where the user lands once the alias is deleted; the author it belonged to. */
  deleteReturnUrl = '/authors';

  elementId = 0;
  private groupFields: MemberFields = EMPTY_MEMBER_FIELDS;
  private prodFields: MemberFields = EMPTY_MEMBER_FIELDS;
  private importOriginFields: ImportOriginFields = {};
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
          const author = data.refs['authorId'] ?? null;
          if (author) {
            this.deleteReturnUrl = `/author/${author.id}`;
          }
          this.form.patchValue({
            title: String(data.fields['title'] ?? ''),
            author,
            startDate: String(data.fields['startDate'] ?? ''),
            endDate: String(data.fields['endDate'] ?? ''),
            displayInMusic: !!Number(data.fields['displayInMusic']),
            displayInGraphics: !!Number(data.fields['displayInGraphics']),
          });
          this.groups = data.groups;
          this.groupRoles = data.groupRoles;
          this.prods = data.prods;
          this.prodRoles = data.roles;
          this.importOrigins = data.importOrigins;
          this.importOriginOptions = data.enums['importOrigins'] ?? [];
          // matches what the editor emits on mount, so the tab count is settled
          // before the tab bar is first checked
          this.prodsCount = data.prods.length;
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

  onGroupFields(fields: MemberFields): void {
    this.groupFields = fields;
  }

  onImportOriginFields(fields: ImportOriginFields): void {
    this.importOriginFields = fields;
  }

  onProdFields(fields: MemberFields): void {
    this.prodFields = fields;
    this.prodsCount = Object.keys(fields.roles).length;
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
          addGroupRole: this.groupFields.roles,
          addGroupStartDate: this.groupFields.startDates,
          addGroupEndDate: this.groupFields.endDates,
          addProdRole: this.prodFields.roles,
          importOrigins: this.importOriginFields,
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
