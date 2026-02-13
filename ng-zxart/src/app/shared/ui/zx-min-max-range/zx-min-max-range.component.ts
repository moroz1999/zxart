import {Component, forwardRef, Input, OnChanges, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {MatSliderModule} from '@angular/material/slider';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';

export type ZxMinMaxRangeValue = {
  min: number;
  max: number;
};

@Component({
  selector: 'zx-min-max-range',
  standalone: true,
  imports: [CommonModule, MatSliderModule, ZxCaptionDirective],
  templateUrl: './zx-min-max-range.component.html',
  styleUrls: ['./zx-min-max-range.component.scss'],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxMinMaxRangeComponent),
      multi: true,
    },
  ],
})
export class ZxMinMaxRangeComponent implements ControlValueAccessor, OnChanges {
  @Input() min = 0;
  @Input() max = 100;
  @Input() step = 1;

  value: ZxMinMaxRangeValue = {min: 0, max: 0};
  disabled = false;

  private onChange: (value: ZxMinMaxRangeValue) => void = () => {};
  private onTouched: () => void = () => {};

  ngOnChanges(changes: SimpleChanges): void {
    if (!changes['min'] && !changes['max']) {
      return;
    }
    this.value = this.normalizeValue(this.value.min, this.value.max);
  }

  writeValue(value: ZxMinMaxRangeValue | null): void {
    const next = value ?? {min: this.min, max: this.max};
    this.value = this.normalizeValue(next.min, next.max);
  }

  registerOnChange(fn: (value: ZxMinMaxRangeValue) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }

  onStartInput(event: Event): void {
    const nextMin = Number((event.target as HTMLInputElement).value);
    this.updateValue(nextMin, this.value.max);
  }

  onEndInput(event: Event): void {
    const nextMax = Number((event.target as HTMLInputElement).value);
    this.updateValue(this.value.min, nextMax);
  }

  private updateValue(min: number, max: number): void {
    this.value = this.normalizeValue(min, max);
    this.onChange(this.value);
    this.onTouched();
  }

  private normalizeValue(min: number, max: number): ZxMinMaxRangeValue {
    const safeMin = Number.isFinite(min) ? min : this.min;
    const safeMax = Number.isFinite(max) ? max : this.max;
    const boundedMin = Math.max(this.min, Math.min(safeMin, this.max));
    const boundedMax = Math.max(this.min, Math.min(safeMax, this.max));
    if (boundedMin <= boundedMax) {
      return {min: boundedMin, max: boundedMax};
    }
    return {min: boundedMax, max: boundedMin};
  }
}
