import {Component, forwardRef, Input} from '@angular/core';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';

@Component({
  selector: 'zx-input-range',
  standalone: true,
  templateUrl: './zx-input-range.component.html',
  styleUrl: './zx-input-range.component.scss',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxInputRangeComponent),
      multi: true,
    },
  ],
})
export class ZxInputRangeComponent implements ControlValueAccessor {
  @Input() min = 0;
  @Input() max = 100;
  @Input() step = 1;

  value = '';
  disabled = false;

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

  onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.value = target.value;
    this.onChange(this.value);
  }

  onBlur(): void {
    this.onTouched();
  }

  get numericValue(): number {
    if (this.value === '' || this.value === null || this.value === undefined) {
      return this.min;
    }
    const parsed = Number(this.value);
    if (!Number.isFinite(parsed)) {
      return this.min;
    }
    return this.clamp(parsed);
  }

  get valuePercent(): number {
    const range = this.max - this.min;
    if (range <= 0) {
      return 0;
    }
    return ((this.numericValue - this.min) / range) * 100;
  }

  private clamp(value: number): number {
    return Math.min(this.max, Math.max(this.min, value));
  }
}
