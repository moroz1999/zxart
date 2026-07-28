import {ChangeDetectionStrategy, Component, forwardRef, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';

export interface ZxSelectOption {
  value: string;
  label: string;
}

@Component({
  selector: 'zx-select',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-select.component.html',
  styleUrl: './zx-select.component.scss',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxSelectComponent),
      multi: true
    }
  ],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSelectComponent implements ControlValueAccessor {
  @Input() placeholder = '';
  @Input() multiple = false;
  @Input() listSize = 1;

  @Input()
  set options(options: ZxSelectOption[] | null | undefined) {
    this.optionList = options ?? [];
    this.syncControlWithDisplay();
  }

  get options(): ZxSelectOption[] {
    return this.optionList;
  }

  @HostBinding('class.zx-select--multiple')
  get multipleClass(): boolean {
    return this.multiple;
  }

  value: string | string[] = '';
  disabled = false;
  touched = false;

  private optionList: ZxSelectOption[] = [];
  /** What the form control holds, as opposed to what the `<select>` displays. */
  private controlValue: string | string[] = '';
  /** Null until the accessor is wired up — nothing can reach the control before that. */
  private onChange: ((value: string | string[]) => void) | null = null;
  private onTouched: () => void = () => {};

  writeValue(value: string | string[]): void {
    if (this.multiple) {
      this.value = Array.isArray(value) ? value : (value ? [value] : []);
      this.controlValue = this.value;
      return;
    }
    this.value = Array.isArray(value) ? (value[0] ?? '') : (value ?? '');
    this.controlValue = this.value;
    this.syncControlWithDisplay();
  }

  registerOnChange(fn: (value: string | string[]) => void): void {
    this.onChange = fn;
    this.syncControlWithDisplay();
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.syncControlWithDisplay();
  }

  onSelectionChange(event: Event): void {
    const target = event.target as HTMLSelectElement;
    if (this.multiple) {
      const values = Array.from(target.selectedOptions).map(option => option.value);
      this.value = values;
      this.controlValue = values;
      this.onChange?.(values);
      return;
    }
    this.value = target.value;
    this.controlValue = target.value;
    this.onChange?.(target.value);
  }

  onBlur(): void {
    if (!this.touched) {
      this.touched = true;
      this.onTouched();
    }
  }

  /**
   * Holds the one invariant this component owes its host: the form control must
   * hold what the `<select>` shows. A single select without an empty option
   * falls back to its first option whenever the value matches none of them —
   * that is the browser's own behaviour — so a form left untouched has to submit
   * that option rather than an empty string. Lists where "nothing" is a valid
   * choice carry an empty option or a placeholder and are never adopted from.
   *
   * Every entry point calls this, and it is idempotent, so the invariant does
   * not depend on the order in which Angular sets the options, writes the value
   * and registers the callback. `controlValue` is what the control holds, kept
   * apart from the displayed `value` precisely so a display fallback that could
   * not be reported yet (no callback registered) is still pending afterwards.
   */
  private syncControlWithDisplay(): void {
    if (this.multiple || this.placeholder !== '' || this.optionList.length === 0) {
      return;
    }
    const displayed = this.optionList.some(option => option.value === this.value)
      ? this.value
      : this.optionList[0].value;
    this.value = displayed;
    // a disabled control is not the view's to change; enabling re-runs this
    if (displayed === this.controlValue || this.disabled || this.onChange === null) {
      return;
    }
    this.controlValue = displayed;
    this.onChange(displayed);
  }

  isSelected(value: string): boolean {
    if (this.multiple) {
      return Array.isArray(this.value) && this.value.includes(value);
    }
    return this.value === value;
  }
}
