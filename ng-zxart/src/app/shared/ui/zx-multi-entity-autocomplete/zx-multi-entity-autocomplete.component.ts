import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, Input} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {EntityRef} from '../../models/entity-ref';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxEntityAutocompleteComponent} from '../zx-entity-autocomplete/zx-entity-autocomplete.component';

/**
 * Multi-select relation picker (groups, publishers, compilation/series prods, …)
 * for forms. Implements ControlValueAccessor: the value is an `EntityRef[]`
 * (the form submits the list of `id`s). Wraps the single
 * {@link ZxEntityAutocompleteComponent} for adding and lists picked items as
 * removable chips. Duplicate ids are ignored.
 */
@Component({
  selector: 'zx-multi-entity-autocomplete',
  standalone: true,
  imports: [CommonModule, FormsModule, ZxButtonComponent, ZxEntityAutocompleteComponent],
  templateUrl: './zx-multi-entity-autocomplete.component.html',
  styleUrl: './zx-multi-entity-autocomplete.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxMultiEntityAutocompleteComponent),
      multi: true,
    },
  ],
})
export class ZxMultiEntityAutocompleteComponent implements ControlValueAccessor {
  /** Comma-separated structureTypes to search, e.g. `group` or `zxProd,zxRelease`. */
  @Input({required: true}) types!: string;
  @Input() placeholder = '';

  items: EntityRef[] = [];
  disabled = false;
  /** Reset target for the inner single picker so it clears after each add. */
  picked: EntityRef | null = null;

  private onChange: (value: EntityRef[]) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  constructor(private readonly cdr: ChangeDetectorRef) {}

  onSelected(ref: EntityRef | null): void {
    if (ref && !this.items.some(item => item.id === ref.id)) {
      this.items = [...this.items, ref];
      this.emit();
    }
    // clear the inner picker so the next item can be added
    this.picked = null;
  }

  onRemove(id: number): void {
    this.items = this.items.filter(item => item.id !== id);
    this.emit();
  }

  writeValue(value: EntityRef[] | null): void {
    this.items = value ? [...value] : [];
    this.cdr.markForCheck();
  }

  registerOnChange(fn: (value: EntityRef[]) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.cdr.markForCheck();
  }

  private emit(): void {
    this.onChange(this.items);
    this.onTouched();
  }
}
