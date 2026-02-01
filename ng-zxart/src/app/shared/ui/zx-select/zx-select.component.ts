import {Component, forwardRef, Input} from '@angular/core';
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

  value = '';
  disabled = false;
  touched = false;

  private onChange: (value: string) => void = () => {};
  private onTouched: () => void = () => {};

  writeValue(value: string): void {
    this.value = value ?? '';
  }

  registerOnChange(fn: (value: string) => void): void {
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
    this.value = target.value;
    this.onChange(this.value);
  }

  onBlur(): void {
    if (!this.touched) {
      this.touched = true;
      this.onTouched();
    }
  }
}
