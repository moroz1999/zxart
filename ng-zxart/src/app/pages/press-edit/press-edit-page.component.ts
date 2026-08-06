import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxCheckboxFieldComponent} from '../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxTextareaComponent} from '../../shared/ui/zx-textarea/zx-textarea.component';
import {ZxMultilangFieldComponent} from '../../shared/ui/zx-multilang-field/zx-multilang-field.component';
import {ZxMultiEntityAutocompleteComponent} from '../../shared/ui/zx-multi-entity-autocomplete/zx-multi-entity-autocomplete.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxDeleteEntityButtonComponent} from '../../shared/ui/zx-delete-entity-button/zx-delete-entity-button.component';
import {EntityRef} from '../../shared/models/entity-ref';
import {FormLanguage} from '../../shared/models/form-data-response';
import {FormDataApiService} from '../../shared/services/form-data-api.service';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';
import {PageMetadataService} from '../../shared/services/page-metadata.service';

/** Multi-relation fields: form control name → search types. */
const RELATION_TYPES: Record<string, string> = {
  authors: 'author,authorAlias',
  people: 'author,authorAlias',
  software: 'zxProd',
  groups: 'group,groupAlias',
  parties: 'party',
  tunes: 'zxMusic',
  pictures: 'zxPicture',
};

/** Routed page for `press/:id/edit` and `prod/:id/articles/add`. */
@Component({
  selector: 'zx-press-edit-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxCheckboxFieldComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxTextareaComponent,
    ZxMultilangFieldComponent,
    ZxMultiEntityAutocompleteComponent,
    ZxFormSectionComponent,
    ZxButtonControlsComponent,
    ZxSpinnerComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
    ZxDeleteEntityButtonComponent,
  ],
  templateUrl: './press-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PressEditPageComponent implements OnInit, OnDestroy {
  readonly relationFields = Object.keys(RELATION_TYPES);
  readonly relationTypes = RELATION_TYPES;

  readonly form = this.fb.group({
    title: this.fb.nonNullable.control<Record<string, string>>({}),
    externalLink: this.fb.nonNullable.control(''),
    authors: this.fb.nonNullable.control<EntityRef[]>([]),
    people: this.fb.nonNullable.control<EntityRef[]>([]),
    software: this.fb.nonNullable.control<EntityRef[]>([]),
    groups: this.fb.nonNullable.control<EntityRef[]>([]),
    parties: this.fb.nonNullable.control<EntityRef[]>([]),
    tunes: this.fb.nonNullable.control<EntityRef[]>([]),
    pictures: this.fb.nonNullable.control<EntityRef[]>([]),
    introduction: this.fb.nonNullable.control<Record<string, string>>({}),
    content: this.fb.nonNullable.control<Record<string, string>>({}),
    originalContent: this.fb.nonNullable.control(''),
    allowComments: this.fb.nonNullable.control(false),
  });

  loading = true;
  submitting = false;
  errorMessage = '';
  languages: FormLanguage[] = [];

  /** Creation mode: a new article of the production the form was opened from. */
  creating = false;

  /** What the heading and the browser tab name: the article, or its production. */
  pageSubject = '';

  /** Where the user lands once the article is deleted; the production it belonged to. */
  deleteReturnUrl = '/prods';

  elementId = 0;
  /** The production the new article belongs to. */
  private prodId = 0;
  private returnUrl = '/';
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
      this.prodId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/prod/${this.prodId}`;
    } else {
      this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
      this.returnUrl = `/press/${this.elementId}`;
    }
    const formData$ = this.creating
      ? this.formData.loadCreate('pressArticle', [], undefined, this.prodId)
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
          const prod = data.parent ?? null;
          if (prod) {
            this.deleteReturnUrl = `/prod/${prod.id}`;
          }
          // a new article has no title yet, so the production it is written about
          // names the page; an existing one falls back to it when left untitled
          this.pageSubject = this.creating
            ? prod?.title ?? ''
            : data.entityTitle || (prod?.title ?? '');
          this.pageMetadata.applyFormTitle(this.route.snapshot, this.pageSubject);
          this.languages = data.languages;
          this.form.patchValue({
            title: data.multilang['title'] ?? {},
            externalLink: String(data.fields['externalLink'] ?? ''),
            authors: data.multiRefs['authors'] ?? [],
            people: data.multiRefs['people'] ?? [],
            software: data.multiRefs['software'] ?? [],
            groups: data.multiRefs['groups'] ?? [],
            parties: data.multiRefs['parties'] ?? [],
            tunes: data.multiRefs['tunes'] ?? [],
            pictures: data.multiRefs['pictures'] ?? [],
            introduction: data.multilang['introduction'] ?? {},
            content: data.multilang['content'] ?? {},
            originalContent: String(data.fields['originalContent'] ?? ''),
            allowComments: !!Number(data.fields['allowComments']),
          });
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: (err: HttpErrorResponse) => {
          this.loading = false;
          this.errorMessage = err.error?.errorMessage ?? 'press-form.error-load';
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

  onSubmit(): void {
    this.submitting = true;
    this.errorMessage = '';
    const value = this.form.getRawValue();
    const ids = (refs: EntityRef[]): string[] => refs.map(ref => String(ref.id));
    const payload = {
      multilang: {
        title: value.title,
        introduction: value.introduction,
        content: value.content,
      },
      fields: {
        externalLink: value.externalLink,
        authors: ids(value.authors),
        people: ids(value.people),
        software: ids(value.software),
        groups: ids(value.groups),
        parties: ids(value.parties),
        tunes: ids(value.tunes),
        pictures: ids(value.pictures),
        originalContent: value.originalContent,
        allowComments: value.allowComments ? '1' : '',
      },
    };
    const save$ = this.creating
      ? this.formSave.create('pressArticle', payload, undefined, this.prodId)
      : this.formSave.save(this.elementId, payload);
    this.subscriptions.add(
      save$.subscribe({
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'press-form.error-save';
            this.cdr.markForCheck();
            return;
          }
          this.router.navigateByUrl(`/press/${result.id}`);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'press-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
