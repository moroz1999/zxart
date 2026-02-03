import {Component, EventEmitter, forwardRef, Input, Output} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';

@Component({
  selector: 'zx-checkbox',
  standalone: true,
  templateUrl: './zx-checkbox.component.html',
  styleUrl: './zx-checkbox.component.scss',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxCheckboxComponent),
      multi: true
    }
  ]
})
export class ZxCheckboxComponent implements ControlValueAccessor {
  @Input() checked = false;
  @Input() disabled = false;
  @Output() checkedChange = new EventEmitter<boolean>();

  touched = false;

  private onChange: (value: boolean) => void = () => {};
  private onTouched: () => void = () => {};

  writeValue(value: boolean): void {
    this.checked = value ?? false;
  }

  registerOnChange(fn: (value: boolean) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }

  toggle(): void {
    if (this.disabled) {
      return;
    }
    this.checked = !this.checked;
    this.onChange(this.checked);
    this.checkedChange.emit(this.checked);
    this.markAsTouched();
  }

  private markAsTouched(): void {
    if (!this.touched) {
      this.touched = true;
      this.onTouched();
    }
  }
}
