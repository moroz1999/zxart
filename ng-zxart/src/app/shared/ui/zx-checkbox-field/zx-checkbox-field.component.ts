import {Component, forwardRef, Input} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {ZxCheckboxComponent} from '../zx-checkbox/zx-checkbox.component';

@Component({
  selector: 'zx-checkbox-field',
  standalone: true,
  imports: [ZxCheckboxComponent],
  templateUrl: './zx-checkbox-field.component.html',
  styleUrl: './zx-checkbox-field.component.scss',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxCheckboxFieldComponent),
      multi: true
    }
  ]
})
export class ZxCheckboxFieldComponent implements ControlValueAccessor {
  @Input() label = '';

  checked = false;
  disabled = false;

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
    this.onTouched();
  }
}
