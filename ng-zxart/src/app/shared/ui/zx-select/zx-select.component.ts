import {Component, forwardRef, HostBinding, Input} from '@angular/core';
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
  ]
})
export class ZxSelectComponent implements ControlValueAccessor {
  @Input() options: ZxSelectOption[] = [];
  @Input() placeholder = '';
  @Input() multiple = false;
  @Input() listSize = 1;

  @HostBinding('class.zx-select--multiple')
  get multipleClass(): boolean {
    return this.multiple;
  }

  value: string | string[] = '';
  disabled = false;
  touched = false;

  private onChange: (value: string | string[]) => void = () => {};
  private onTouched: () => void = () => {};

  writeValue(value: string | string[]): void {
    if (this.multiple) {
      this.value = Array.isArray(value) ? value : (value ? [value] : []);
      return;
    }
    this.value = Array.isArray(value) ? (value[0] ?? '') : (value ?? '');
  }

  registerOnChange(fn: (value: string | string[]) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }

  onSelectionChange(event: Event): void {
    const target = event.target as HTMLSelectElement;
    if (this.multiple) {
      const values = Array.from(target.selectedOptions).map(option => option.value);
      this.value = values;
      this.onChange(values);
      return;
    }
    this.value = target.value;
    this.onChange(target.value);
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
