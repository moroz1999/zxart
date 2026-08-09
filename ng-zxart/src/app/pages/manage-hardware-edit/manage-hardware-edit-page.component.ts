import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, ReactiveFormsModule, Validators} from '@angular/forms';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subscription, switchMap, take} from 'rxjs';
import {HardwareCatalogDto, HardwareSaveRequest} from '../../features/manage-hardware/models/hardware-catalog.dto';
import {ManageHardwareApiService} from '../../features/manage-hardware/services/manage-hardware-api.service';
import {ConfirmDialogService} from '../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxControlErrorsComponent} from '../../shared/ui/zx-form/zx-control-errors/zx-control-errors.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxFormSectionComponent} from '../../shared/ui/zx-form/zx-form-section/zx-form-section.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxSelectComponent, ZxSelectOption} from '../../shared/ui/zx-select/zx-select.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

const LIST_URL = '/manage/hardware';

/**
 * Create or edit one hardware item (`/manage/hardware/add`, `/manage/hardware/:id`).
 *
 * All interface languages are edited at once — an item without a label in some
 * language would show its bare code to that whole audience, and the backend
 * refuses to save it anyway.
 */
@Component({
  selector: 'zx-manage-hardware-edit-page',
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
    ZxFormSectionComponent,
    ZxFormDirective,
    ZxInputComponent,
    ZxSelectComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './manage-hardware-edit-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageHardwareEditPageComponent implements OnInit, OnDestroy {
  readonly form = this.fb.group({
    code: this.fb.nonNullable.control('', Validators.required),
    category: this.fb.nonNullable.control('', Validators.required),
    position: this.fb.nonNullable.control(0),
  });

  readonly codeMessages = {required: 'manage-hardware.error-code-required'};

  creating = false;
  loading = true;
  submitting = false;
  errorMessage = '';
  usages = 0;
  languages: string[] = [];
  categoryOptions: ZxSelectOption[] = [];

  /** One name + short name control pair per language, added once the catalog is known. */
  readonly names = this.fb.group({});

  private elementId = 0;
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly api: ManageHardwareApiService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.creating = this.route.snapshot.data['create'] === true;
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;

    this.subscriptions.add(
      this.api.catalog$.pipe(take(1)).subscribe(catalog => {
        this.applyCatalog(catalog);
        this.loading = false;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  nameControlName(language: string): string {
    return `${language}-name`;
  }

  shortNameControlName(language: string): string {
    return `${language}-shortName`;
  }

  onCancel(): void {
    this.router.navigateByUrl(LIST_URL);
  }

  onDelete(): void {
    const keys = [
      'manage-hardware.delete-confirm-title',
      'manage-hardware.delete-confirm-message',
      'manage-hardware.delete',
      'form.cancel',
    ];
    this.subscriptions.add(
      this.translate.get(keys).pipe(
        switchMap((texts: Record<string, string>) => this.confirmDialog.confirm({
          title: texts['manage-hardware.delete-confirm-title'],
          message: texts['manage-hardware.delete-confirm-message'],
          confirmLabel: texts['manage-hardware.delete'],
          cancelLabel: texts['form.cancel'],
          danger: true,
        })),
      ).subscribe(confirmed => {
        if (confirmed) {
          this.runDelete();
        }
      }),
    );
  }

  onSubmit(): void {
    if (this.form.invalid || this.names.invalid) {
      this.form.markAllAsTouched();
      this.names.markAllAsTouched();
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
          this.errorMessage = error.error?.errorMessage ?? 'manage-hardware.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private runDelete(): void {
    this.submitting = true;
    this.errorMessage = '';
    this.subscriptions.add(
      this.api.delete(this.elementId).subscribe({
        next: () => this.router.navigateByUrl(LIST_URL),
        error: (error: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = error.error?.errorMessage ?? 'manage-hardware.error-delete';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private applyCatalog(catalog: HardwareCatalogDto): void {
    this.languages = catalog.languages;
    this.categoryOptions = catalog.categories.map(code => ({
      value: code,
      label: code,
    }));

    for (const language of catalog.languages) {
      this.names.addControl(this.nameControlName(language), this.fb.nonNullable.control('', Validators.required));
      this.names.addControl(this.shortNameControlName(language), this.fb.nonNullable.control('', Validators.required));
    }

    const item = this.creating ? null : catalog.items.find(candidate => candidate.id === this.elementId) ?? null;
    if (item === null) {
      // a new item lands at the end of the first category by default
      this.form.patchValue({category: catalog.categories[0] ?? '', position: this.nextPosition(catalog)});
      return;
    }

    this.usages = item.usages;
    this.form.patchValue({code: item.code, category: item.category, position: item.position});
    for (const language of catalog.languages) {
      this.names.patchValue({
        [this.nameControlName(language)]: item.names[language]?.name ?? '',
        [this.shortNameControlName(language)]: item.names[language]?.shortName ?? '',
      });
    }
  }

  private nextPosition(catalog: HardwareCatalogDto): number {
    return catalog.items.reduce((max, item) => Math.max(max, item.position), 0) + 1;
  }

  private buildRequest(): HardwareSaveRequest {
    const value = this.form.getRawValue();
    const names: HardwareSaveRequest['names'] = {};
    for (const language of this.languages) {
      names[language] = {
        name: String(this.names.get(this.nameControlName(language))?.value ?? ''),
        shortName: String(this.names.get(this.shortNameControlName(language))?.value ?? ''),
      };
    }

    return {
      ...(this.creating ? {} : {id: this.elementId}),
      code: value.code,
      category: value.category,
      position: value.position,
      names,
    };
  }
}
