import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, Input} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {CategoryTreeNode} from '../../models/form-data-response';

interface CategoryRow {
  id: number;
  title: string;
  indent: number;
}

/**
 * Multi-select picker over the prod-category tree. Implements
 * ControlValueAccessor: the value is the list of selected category ids
 * (`number[]`), which the form posts as `categories[]`. The tree comes from
 * `/formdata/` as a flat, depth-first list with a `level`; rows are indented by
 * their relative depth.
 */
@Component({
  selector: 'zx-category-tree-select',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './zx-category-tree-select.component.html',
  styleUrl: './zx-category-tree-select.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxCategoryTreeSelectComponent),
      multi: true,
    },
  ],
})
export class ZxCategoryTreeSelectComponent implements ControlValueAccessor {
  @Input()
  set nodes(value: CategoryTreeNode[]) {
    const minLevel = value.length ? Math.min(...value.map(n => n.level)) : 0;
    this.rows = value.map(n => ({id: n.id, title: n.title, indent: n.level - minLevel}));
    this.cdr.markForCheck();
  }

  rows: CategoryRow[] = [];
  disabled = false;

  private selected = new Set<number>();

  private onChange: (value: number[]) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  constructor(private readonly cdr: ChangeDetectorRef) {}

  isSelected(id: number): boolean {
    return this.selected.has(id);
  }

  onToggle(id: number, checked: boolean): void {
    if (checked) {
      this.selected.add(id);
    } else {
      this.selected.delete(id);
    }
    this.onChange([...this.selected]);
    this.onTouched();
  }

  writeValue(value: number[] | null): void {
    this.selected = new Set(value ?? []);
    this.cdr.markForCheck();
  }

  registerOnChange(fn: (value: number[]) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
    this.cdr.markForCheck();
  }
}
