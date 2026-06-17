import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, HostBinding, Input, OnChanges} from '@angular/core';
import {AbstractControl} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable, of, startWith} from 'rxjs';
import {TextDirective} from '../../typography/directives/text.directive';

/** Map of a control's validator error key (e.g. `required`) to an i18n translation key. */
export type ZxControlErrorMessages = Record<string, string>;

/**
 * Renders the validation message for a single form control. Reads the bound `AbstractControl`,
 * maps its first matching validator error to a translation key and shows it once the control is
 * touched/dirty. Reactive to the control's state via `AbstractControl.events` (OnPush-safe).
 * Place it next to the control, inside `zx-form-control`.
 */
@Component({
  selector: 'zx-control-errors',
  standalone: true,
  imports: [CommonModule, TranslateModule, TextDirective],
  templateUrl: './zx-control-errors.component.html',
  styleUrl: './zx-control-errors.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxControlErrorsComponent implements OnChanges {
  @Input({required: true}) control: AbstractControl | null = null;
  @Input() messages: ZxControlErrorMessages = {};
  @Input() showOn: 'touched' | 'dirty' | 'always' = 'touched';

  @HostBinding('attr.role') readonly role = 'alert';

  messageKey$: Observable<string | null> = of(null);

  ngOnChanges(): void {
    const control = this.control;
    if (control === null) {
      this.messageKey$ = of(null);
      return;
    }

    this.messageKey$ = control.events.pipe(
      startWith(null),
      map(() => this.resolveMessageKey(control)),
    );
  }

  private resolveMessageKey(control: AbstractControl): string | null {
    if (control.errors === null || !this.isVisible(control)) {
      return null;
    }

    for (const errorKey of Object.keys(control.errors)) {
      const translationKey = this.messages[errorKey];
      if (translationKey !== undefined) {
        return translationKey;
      }
    }

    return null;
  }

  private isVisible(control: AbstractControl): boolean {
    if (this.showOn === 'always') {
      return true;
    }
    return this.showOn === 'touched' ? control.touched : control.dirty;
  }
}
