import {Component, ElementRef, EventEmitter, forwardRef, Input, Output, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {MatAutocomplete, MatAutocompleteTrigger} from '@angular/material/autocomplete';

@Component({
  selector: 'zx-input',
  standalone: true,
  imports: [CommonModule, MatAutocompleteTrigger],
  templateUrl: './zx-input.component.html',
  styleUrl: './zx-input.component.scss',
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxInputComponent),
      multi: true
    }
  ]
})
export class ZxInputComponent implements ControlValueAccessor {
  @Input() placeholder = '';
  @Input() type: 'text' | 'email' | 'password' | 'number' = 'text';
  @Input() matAutocomplete?: MatAutocomplete;
  @Output() keyup = new EventEmitter<KeyboardEvent>();

  @ViewChild('inputElement') inputElementRef?: ElementRef<HTMLInputElement>;

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

  onInput(event: Event): void {
    const target = event.target as HTMLInputElement;
    this.value = target.value;
    this.onChange(this.value);
  }

  onBlur(): void {
    if (!this.touched) {
      this.touched = true;
      this.onTouched();
    }
  }

  onKeyup(event: KeyboardEvent): void {
    this.keyup.emit(event);
  }

  get nativeElement(): HTMLInputElement | undefined {
    return this.inputElementRef?.nativeElement;
  }

  clear(): void {
    this.value = '';
    if (this.inputElementRef) {
      this.inputElementRef.nativeElement.value = '';
    }
    this.onChange(this.value);
  }
}
