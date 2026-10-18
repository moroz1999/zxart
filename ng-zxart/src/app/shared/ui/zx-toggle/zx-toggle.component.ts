import {ChangeDetectionStrategy, ChangeDetectorRef, Component, EventEmitter, forwardRef, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {SvgIconComponent} from 'angular-svg-icon';

export interface ZxToggleOption {
  value: string;
  icon?: string;
  label?: string;
}

/**
 * Segmented control picking one of a few options. Bound either through
 * `value`/`valueChange` or as a form control.
 */
@Component({
  selector: 'zx-toggle',
  standalone: true,
  imports: [CommonModule, SvgIconComponent],
  templateUrl: './zx-toggle.component.html',
  styleUrl: './zx-toggle.component.scss',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxToggleComponent),
      multi: true,
    },
  ],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxToggleComponent implements ControlValueAccessor {
  @Input() options: ZxToggleOption[] = [];
  @Input() value: string = '';
  @Input() size: 'xs' | 'sm' | 'md' = 'md';
  @Input() ariaLabel = '';
  @Output() valueChange = new EventEmitter<string>();

  disabled = false;

  private onChange: (value: string) => void = () => {};
  private onTouched: () => void = () => {};

  constructor(private readonly cdr: ChangeDetectorRef) {}

  writeValue(value: string | null): void {
    this.value = value ?? '';
    this.cdr.markForCheck();
  }

  registerOnChange(fn: (value: string) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.cdr.markForCheck();
  }

  onValueChange(newValue: string): void {
    if (this.disabled) {
      return;
    }
    this.value = newValue;
    this.valueChange.emit(newValue);
    this.onChange(newValue);
    this.onTouched();
  }

  trackByValue(_index: number, option: ZxToggleOption): string {
    return option.value;
  }
}
