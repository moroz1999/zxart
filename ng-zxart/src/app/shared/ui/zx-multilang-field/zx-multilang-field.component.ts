import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, forwardRef, HostBinding, Input} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {FormLanguage} from '../../models/form-data-response';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxTextareaComponent} from '../zx-textarea/zx-textarea.component';
import {TextDirective} from '../typography/directives/text.directive';

/**
 * Multi-language text field for forms: one input per language. Implements
 * ControlValueAccessor; the value is a `{languageId: value}` map (matching the
 * legacy `formData[{id}][languageId][field]` structure).
 */
@Component({
  selector: 'zx-multilang-field',
  standalone: true,
  imports: [CommonModule, FormsModule, ZxInputComponent, ZxTextareaComponent, TextDirective],
  templateUrl: './zx-multilang-field.component.html',
  styleUrl: './zx-multilang-field.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxMultilangFieldComponent),
      multi: true,
    },
  ],
})
export class ZxMultilangFieldComponent implements ControlValueAccessor {
  @Input() languages: FormLanguage[] = [];
  @Input() columns = 1;
  /** Render a textarea per language instead of a single-line input. */
  @Input() multiline = false;
  @Input() rows = 4;

  @HostBinding('style.--zx-multilang-field-columns')
  get columnsVariable(): string {
    return String(this.columns);
  }

  value: Record<string, string> = {};
  disabled = false;

  valueFor(languageId: number): string {
    return this.value[languageId] ?? '';
  }

  private onChange: (value: Record<string, string>) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  onInput(languageId: number, value: string): void {
    this.value = {...this.value, [languageId]: value};
    this.onChange(this.value);
    this.onTouched();
  }

  writeValue(value: Record<string, string> | null): void {
    this.value = value ?? {};
  }

  registerOnChange(fn: (value: Record<string, string>) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }
}
