import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule} from '@angular/forms';
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
import {ZxEntityAutocompleteComponent} from '../../shared/ui/zx-entity-autocomplete/zx-entity-autocomplete.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {EntityRef} from '../../shared/models/entity-ref';
import {FormSaveApiService} from '../../shared/services/form-save-api.service';

interface JoinPicker {
  field: string;
  labelKey: string;
  types: string;
}

interface JoinCheckbox {
  field: string;
  labelKey: string;
}

/**
 * Generic merge/join form (`<entity>/:id/join`). Renders an entity picker per
 * merge target (`joinAsAlias` / `joinAndDelete`) plus optional checkboxes, posts
 * the legacy `join` action, then navigates to the picked target entity.
 */
@Component({
  selector: 'zx-join-form-page',
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
    ZxEntityAutocompleteComponent,
    ZxStackComponent,
  ],
  templateUrl: './join-form-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class JoinFormPageComponent implements OnInit, OnDestroy {
  form: FormGroup = this.fb.group({});
  pickers: JoinPicker[] = [];
  checkboxes: JoinCheckbox[] = [];

  submitting = false;
  errorMessage = '';

  private elementId = 0;
  private entityPath = '';
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly fb: FormBuilder,
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly cdr: ChangeDetectorRef,
    private readonly formSave: FormSaveApiService,
  ) {}

  ngOnInit(): void {
    this.elementId = Number(this.route.snapshot.paramMap.get('id')) || 0;
    this.entityPath = this.route.snapshot.data['entityPath'] ?? '';
    this.pickers = (this.route.snapshot.data['pickers'] ?? []) as JoinPicker[];
    this.checkboxes = (this.route.snapshot.data['checkboxes'] ?? []) as JoinCheckbox[];

    for (const picker of this.pickers) {
      this.form.addControl(picker.field, this.fb.control<EntityRef | null>(null));
    }
    for (const checkbox of this.checkboxes) {
      this.form.addControl(checkbox.field, this.fb.nonNullable.control(false));
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSubmit(): void {
    const value = this.form.getRawValue();
    let target: number | null = null;
    const fields: Record<string, string> = {};
    for (const picker of this.pickers) {
      const ref = value[picker.field] as EntityRef | null;
      fields[picker.field] = ref ? String(ref.id) : '';
      if (ref && target === null) {
        target = ref.id;
      }
    }
    if (target === null) {
      this.errorMessage = 'join-form.error-no-target';
      return;
    }
    for (const checkbox of this.checkboxes) {
      fields[checkbox.field] = value[checkbox.field] ? '1' : '';
    }

    this.submitting = true;
    this.errorMessage = '';
    this.subscriptions.add(
      this.formSave.save(this.elementId, {fields}, 'join').subscribe({
        next: () => this.router.navigateByUrl(`/${this.entityPath}/${target}`),
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'join-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
