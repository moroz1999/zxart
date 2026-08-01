import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';

export interface ZxSelectOption {
  value: string;
  label: string;
}

/**
 * Select bound to a form control. The control owns the value: the component
 * displays what the control holds and reports only what the user picks, never
 * a value of its own. A default therefore belongs to the form that creates the
 * control, not here.
 */
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
  @Input() options: ZxSelectOption[] = [];

  @HostBinding('class.zx-select--multiple')
  get multipleClass(): boolean {
    return this.multiple;
  }

  value: string | string[] = '';
  disabled = false;
  touched = false;

  /** Null until the accessor is wired up — nothing can reach the control before that. */
  private onChange: ((value: string | string[]) => void) | null = null;
  private onTouched: () => void = () => {};

  constructor(private readonly cdr: ChangeDetectorRef) {}

  /**
   * A single native select always shows one of its options, so a control value
   * that matches none of them gets a blank option to show instead — otherwise
   * the select would display an option the control does not hold, and the form
   * would submit an empty value behind it. Lists where "nothing" is a valid
   * choice carry their own empty option or a placeholder and never need it.
   */
  get blankOption(): boolean {
    return !this.multiple
      && this.placeholder === ''
      && !this.options.some(option => option.value === this.value);
  }

  writeValue(value: string | string[] | null): void {
    if (this.multiple) {
      this.value = Array.isArray(value) ? value : (value ? [value] : []);
    } else {
      this.value = Array.isArray(value) ? (value[0] ?? '') : (value ?? '');
    }
    this.cdr.markForCheck();
  }

  registerOnChange(fn: (value: string | string[]) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.cdr.markForCheck();
  }

  onSelectionChange(event: Event): void {
    const target = event.target as HTMLSelectElement;
    if (this.multiple) {
      const values = Array.from(target.selectedOptions).map(option => option.value);
      this.value = values;
      this.onChange?.(values);
      return;
    }
    this.value = target.value;
    this.onChange?.(target.value);
  }

  onBlur(): void {
    if (!this.touched) {
      this.touched = true;
      this.onTouched();
    }
  }

  isSelected(value: string): boolean {
    if (this.multiple) {
      return Array.isArray(this.value) && this.value.includes(value);
    }
    return this.value === value;
  }
}
