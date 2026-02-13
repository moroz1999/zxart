import {Component, ElementRef, forwardRef, Input, OnChanges, SimpleChanges, ViewChild} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ControlValueAccessor, NG_VALUE_ACCESSOR} from '@angular/forms';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';

export type ZxMinMaxRangeValue = {
  min: number;
  max: number;
};

type ZxRangeThumb = 'min' | 'max';

@Component({
  selector: 'zx-min-max-range',
  standalone: true,
  imports: [CommonModule, ZxCaptionDirective],
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
  @ViewChild('slider', {static: true}) sliderRef!: ElementRef<HTMLElement>;

  value: ZxMinMaxRangeValue = {min: 0, max: 0};
  disabled = false;
  private activeThumb: ZxRangeThumb | null = null;

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

  onBlur(): void {
    this.onTouched();
  }

  get minPercent(): number {
    return this.toPercent(this.value.min);
  }

  get maxPercent(): number {
    return this.toPercent(this.value.max);
  }

  ngOnDestroy(): void {
    this.teardownDragListeners();
  }

  onTrackPointerDown(event: PointerEvent): void {
    if (this.disabled) {
      return;
    }
    const value = this.valueFromClientX(event.clientX);
    const thumb = this.closestThumb(value);
    this.setThumbValue(thumb, value, true);
    this.startDrag(thumb, event);
  }

  onThumbPointerDown(event: PointerEvent, thumb: ZxRangeThumb): void {
    if (this.disabled) {
      return;
    }
    event.stopPropagation();
    this.startDrag(thumb, event);
  }

  onThumbKeyDown(event: KeyboardEvent, thumb: ZxRangeThumb): void {
    if (this.disabled) {
      return;
    }
    const fineStep = this.safeStep();
    const coarseStep = fineStep * 10;
    let target: number | null = null;
    switch (event.key) {
      case 'ArrowLeft':
      case 'ArrowDown':
        target = this.getThumbValue(thumb) - fineStep;
        break;
      case 'ArrowRight':
      case 'ArrowUp':
        target = this.getThumbValue(thumb) + fineStep;
        break;
      case 'PageDown':
        target = this.getThumbValue(thumb) - coarseStep;
        break;
      case 'PageUp':
        target = this.getThumbValue(thumb) + coarseStep;
        break;
      case 'Home':
        target = this.min;
        break;
      case 'End':
        target = this.max;
        break;
      default:
        return;
    }
    event.preventDefault();
    this.setThumbValue(thumb, target, true);
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

  private startDrag(thumb: ZxRangeThumb, event: PointerEvent): void {
    event.preventDefault();
    this.activeThumb = thumb;
    window.addEventListener('pointermove', this.handlePointerMove);
    window.addEventListener('pointerup', this.handlePointerUp);
    window.addEventListener('pointercancel', this.handlePointerUp);
  }

  private teardownDragListeners(): void {
    window.removeEventListener('pointermove', this.handlePointerMove);
    window.removeEventListener('pointerup', this.handlePointerUp);
    window.removeEventListener('pointercancel', this.handlePointerUp);
    this.activeThumb = null;
  }

  private readonly handlePointerMove = (event: PointerEvent): void => {
    if (!this.activeThumb || this.disabled) {
      return;
    }
    const value = this.valueFromClientX(event.clientX);
    this.setThumbValue(this.activeThumb, value, true);
  };

  private readonly handlePointerUp = (): void => {
    this.teardownDragListeners();
    this.onTouched();
  };

  private valueFromClientX(clientX: number): number {
    const slider = this.sliderRef?.nativeElement;
    if (!slider) {
      return this.min;
    }
    const rect = slider.getBoundingClientRect();
    if (rect.width <= 0) {
      return this.min;
    }
    const ratio = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
    const raw = this.min + ratio * (this.max - this.min);
    return this.roundToStep(raw);
  }

  private setThumbValue(thumb: ZxRangeThumb, nextValue: number, emit: boolean): void {
    const value = this.roundToStep(nextValue);
    if (thumb === 'min') {
      const nextMin = Math.min(value, this.value.max);
      this.value = {min: nextMin, max: this.value.max};
    } else {
      const nextMax = Math.max(value, this.value.min);
      this.value = {min: this.value.min, max: nextMax};
    }
    if (emit) {
      this.onChange(this.value);
      this.onTouched();
    }
  }

  private closestThumb(value: number): ZxRangeThumb {
    const minDistance = Math.abs(value - this.value.min);
    const maxDistance = Math.abs(value - this.value.max);
    return minDistance <= maxDistance ? 'min' : 'max';
  }

  private getThumbValue(thumb: ZxRangeThumb): number {
    return thumb === 'min' ? this.value.min : this.value.max;
  }

  private safeStep(): number {
    return this.step > 0 ? this.step : 1;
  }

  private roundToStep(value: number): number {
    const step = this.safeStep();
    const steps = Math.round((value - this.min) / step);
    const rounded = this.min + steps * step;
    const precision = this.getStepPrecision(step);
    const normalized = Number(rounded.toFixed(precision));
    return Math.max(this.min, Math.min(normalized, this.max));
  }

  private getStepPrecision(step: number): number {
    const stepString = String(step);
    const decimalPoint = stepString.indexOf('.');
    if (decimalPoint === -1) {
      return 0;
    }
    return stepString.length - decimalPoint - 1;
  }

  private toPercent(value: number): number {
    const range = this.max - this.min;
    if (range <= 0) {
      return 0;
    }
    return ((value - this.min) / range) * 100;
  }
}
