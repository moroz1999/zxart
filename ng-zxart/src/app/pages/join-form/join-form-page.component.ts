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
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

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
 * merge mode (`joinAsAlias` / `joinAndDelete`) plus optional checkboxes, and
 * posts the legacy `join` action.
 *
 * The picked entity is absorbed into the current one and ceases to exist, so
 * the form returns to the current entity page — which reloads its details with
 * the merged content.
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
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './join-form-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class JoinFormPageComponent implements OnInit, OnDestroy {
  /** Built from pickers and checkboxes from the route, so its shape is not static and stays untyped. */
  form: FormGroup = this.fb.group({});
  pickers: JoinPicker[] = [];
  checkboxes: JoinCheckbox[] = [];

  submitting = false;
  errorMessage = '';

  private elementId = 0;
  private entityUrl = '';
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
    // the page this form was opened from — this route without its `join` segment
    this.entityUrl = this.router.url.split('?')[0].replace(/\/join\/?$/, '');
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
    let picked = false;
    const fields: Record<string, string> = {};
    for (const picker of this.pickers) {
      const ref = value[picker.field] as EntityRef | null;
      fields[picker.field] = ref ? String(ref.id) : '';
      picked = picked || ref !== null;
    }
    if (!picked) {
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
        next: result => {
          if (result.id <= 0) {
            this.submitting = false;
            this.errorMessage = result.errorMessage ?? 'join-form.error-save';
            this.cdr.markForCheck();
            return;
          }
          this.router.navigateByUrl(this.entityUrl);
        },
        error: (err: HttpErrorResponse) => {
          this.submitting = false;
          this.errorMessage = err.error?.errorMessage ?? 'join-form.error-save';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
