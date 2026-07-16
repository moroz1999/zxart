import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
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
import {ZxImageUploadComponent, ImageUploadChange} from '../../shared/ui/zx-image-upload/zx-image-upload.component';
import {ZxMemberRoleEditorComponent} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.component';
import {MemberFields, MemberRoleItem} from '../../shared/ui/zx-member-role-editor/zx-member-role-editor.models';
import {ZxSubgroupsEditorComponent, SubgroupItem} from '../../shared/ui/zx-subgroups-editor/zx-subgroups-editor.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

const GROUP_TYPES = ['company', 'studio', 'scene', 'store', 'education'];
const EMPTY_MEMBER_FIELDS: MemberFields = {addAuthor: '', addAuthorRole: {}, addAuthorStartDate: {}, addAuthorEndDate: {}};

/** Routed page for `group/:id/edit`. */
@Component({
  selector: 'zx-group-edit-page',
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
    ZxMemberRoleEditorComponent,
    ZxSubgroupsEditorComponent,
    ZxStackComponent,
    ZxSpinnerComponent,
  ],
  templateUrl: './group-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GroupEditPageComponent implements OnInit, OnDestroy {
  readonly groupTypes = GROUP_TYPES;

  readonly form: FormGroup = this.fb.group({
    title: this.fb.nonNullable.control('', Validators.required),
    type: this.fb.nonNullable.control(''),
    country: this.fb.control<EntityRef | null>(null),
    city: this.fb.control<EntityRef | null>(null),
    wikiLink: this.fb.nonNullable.control(''),
    website: this.fb.nonNullable.control(''),
    abbreviation: this.fb.nonNullable.control(''),
    startDate: this.fb.nonNullable.control(''),
    endDate: this.fb.nonNullable.control(''),
    slogan: this.fb.nonNullable.control(''),
  });

  readonly titleMessages = {required: 'group-form.error-title-required'};

  loading = true;
  submitting = false;
  errorMessage = '';
  imageUrl: string | null = null;
  members: MemberRoleItem[] = [];
  roles: string[] = [];
  subgroups: SubgroupItem[] = [];

  private elementId = 0;
  private imageFile: File | null = null;
  private removeImage = false;
  private memberFields: MemberFields = EMPTY_MEMBER_FIELDS;
  private subgroupIds: number[] = [];
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
      this.formData.load(this.elementId, ['country', 'city']).subscribe({
        next: data => {
          this.form.patchValue({
            title: String(data.fields['title'] ?? ''),
            type: String(data.fields['type'] ?? ''),
            country: data.refs['country'] ?? null,
            city: data.refs['city'] ?? null,
            wikiLink: String(data.fields['wikiLink'] ?? ''),
            website: String(data.fields['website'] ?? ''),
            abbreviation: String(data.fields['abbreviation'] ?? ''),
            startDate: String(data.fields['startDate'] ?? ''),
            endDate: String(data.fields['endDate'] ?? ''),
            slogan: String(data.fields['slogan'] ?? ''),
          });
          this.imageUrl = data.images['image'] ?? null;
          this.members = data.members;
          this.roles = data.roles;
          this.subgroups = data.subgroups;
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'group-form.error-load';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onImageChanged(change: ImageUploadChange): void {
    this.imageFile = change.file;
    this.removeImage = change.removed;
  }

  onMemberFields(fields: MemberFields): void {
    this.memberFields = fields;
  }

  onSubgroupIds(ids: number[]): void {
    this.subgroupIds = ids;
  }

  onRemoveMember(authorId: number): void {
    this.subscriptions.add(this.formSave.deleteMember(this.elementId, authorId).subscribe());
  }

  onSubmit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    this.subscriptions.add(
      this.formSave.save(this.elementId, {
        fields: {
          title: value.title,
          type: value.type,
          country: value.country ? String(value.country.id) : '',
          city: value.city ? String(value.city.id) : '',
          wikiLink: value.wikiLink,
          website: value.website,
          abbreviation: value.abbreviation,
          startDate: value.startDate,
          endDate: value.endDate,
          slogan: value.slogan,
          addAuthor: this.memberFields.addAuthor,
          addAuthorRole: this.memberFields.addAuthorRole,
          addAuthorStartDate: this.memberFields.addAuthorStartDate,
          addAuthorEndDate: this.memberFields.addAuthorEndDate,
          subGroupsSelector: this.subgroupIds.map(String),
        },
        image: {field: 'image', file: this.imageFile, remove: this.removeImage},
      }).subscribe({
        next: result => {
          this.router.navigateByUrl(`/group/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'group-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
