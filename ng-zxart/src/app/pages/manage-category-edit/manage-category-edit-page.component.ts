import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subscription, take} from 'rxjs';
import {
  CategorySaveRequest,
  ManageCategoriesDto,
  ManageCategoryDto,
} from '../../features/manage-categories/models/manage-category.dto';
import {ManageCategoriesApiService} from '../../features/manage-categories/services/manage-categories-api.service';
import {FormLanguage} from '../../shared/models/form-data-response';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxMultilangFieldComponent} from '../../shared/ui/zx-multilang-field/zx-multilang-field.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxSelectComponent, ZxSelectOption} from '../../shared/ui/zx-select/zx-select.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

const LIST_URL = '/manage/categories';
/** One level of nesting in the flat parent picker. */
const NESTING_PREFIX = '\u00a0\u00a0\u00a0\u00a0';

/**
 * Create or rename one category (`/manage/categories/add`, `/manage/categories/:id`).
 *
 * All interface languages are edited at once — a category without a title in
 * some language would show a blank label to that whole audience, and the
 * backend refuses to save it anyway.
 *
 * The parent select is where a category is placed and where it is moved: it
 * offers the whole tree plus a "top level" entry, minus the category itself and
 * everything beneath it, which it could not be moved into. A creation started
 * from a row opens with that row selected (`?parent=`).
 */
@Component({
  selector: 'zx-manage-category-edit-page',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormMessageComponent,
    ZxFormDirective,
    ZxMultilangFieldComponent,
    ZxPageLayoutComponent,
    ZxSelectComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
  ],
  templateUrl: './manage-category-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageCategoryEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    titles: this.fb.nonNullable.control<Record<string, string>>({}),
    // '' is the top level; the select deals in strings
    parentId: this.fb.nonNullable.control(''),
  });

  creating = false;
  loading = true;
  submitting = false;
  errorMessage = '';
  languages: FormLanguage[] = [];
  parentOptions: ZxSelectOption[] = [];

  private elementId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly api: ManageCategoriesApiService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.creating = this.route.snapshot.data['create'] === true;
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;

    this.subscriptions.add(
      this.api.tree$.pipe(take(1)).subscribe(tree => {
        this.applyTree(tree);
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCancel(): void {
    this.router.navigateByUrl(LIST_URL);
  }

  onSubmit(): void {
    if (this.hasBlankTitle()) {
      this.errorMessage = 'manage-categories.error-title-required';
      return;
    }

    this.submitting = true;
    this.errorMessage = '';
    const request = this.buildRequest();
    const save$ = this.creating ? this.api.create(request) : this.api.update(request);

    this.subscriptions.add(
      save$.subscribe({
        next: () => this.router.navigateByUrl(LIST_URL),
        error: (error: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = error.error?.errorMessage ?? 'manage-categories.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private applyTree(tree: ManageCategoriesDto): void {
    this.languages = tree.languages;
    this.parentOptions = this.buildParentOptions(tree.categories);

    const category = this.creating
      ? null
      : tree.categories.find(candidate => candidate.id === this.elementId) ?? null;
    if (category === null) {
      const requested = this.route.snapshot.queryParamMap.get('parent') ?? '';
      const offered = this.parentOptions.some(option => option.value === requested);
      this.form.patchValue({titles: this.blankTitles(), parentId: offered ? requested : ''});
      return;
    }

    this.form.patchValue({
      titles: {...this.blankTitles(), ...category.titles},
      parentId: category.parentId === null ? '' : String(category.parentId),
    });
  }

  /**
   * The whole tree as one indented list. Editing a category leaves out its own
   * branch: moving a category under itself is refused by the backend, and an
   * option that can only fail does not belong in the picker.
   */
  private buildParentOptions(categories: ManageCategoryDto[]): ZxSelectOption[] {
    const excluded = this.creating ? new Set<number>() : this.collectBranchIds(categories, this.elementId);

    return [
      {value: '', label: this.translate.instant('manage-categories.top-level')},
      ...categories
        .filter(category => !excluded.has(category.id))
        .map(category => ({
          value: String(category.id),
          label: NESTING_PREFIX.repeat(category.level) + category.title,
        })),
    ];
  }

  /** The category and everything beneath it, from the depth-first list. */
  private collectBranchIds(categories: ManageCategoryDto[], rootId: number): Set<number> {
    const branch = new Set<number>([rootId]);
    for (const category of categories) {
      if (category.parentId !== null && branch.has(category.parentId)) {
        branch.add(category.id);
      }
    }
    return branch;
  }

  private blankTitles(): Record<string, string> {
    const titles: Record<string, string> = {};
    for (const language of this.languages) {
      titles[language.id] = '';
    }
    return titles;
  }

  private hasBlankTitle(): boolean {
    const titles = this.form.getRawValue().titles;
    return this.languages.some(language => (titles[language.id] ?? '').trim() === '');
  }

  private buildRequest(): CategorySaveRequest {
    const value = this.form.getRawValue();
    const parentId = value.parentId === '' ? null : Number(value.parentId);

    return this.creating
      ? {titles: value.titles, parentId}
      : {id: this.elementId, titles: value.titles, parentId};
  }
}
