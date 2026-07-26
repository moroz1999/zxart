import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, Input, OnInit} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {CategoryTreeNode} from '../../models/form-data-response';
import {ChipItem} from '../../models/chip-item';
import {ZxCheckboxComponent} from '../zx-checkbox/zx-checkbox.component';
import {ZxChipsComponent} from '../zx-chips/zx-chips.component';

interface CategoryRow {
  id: number;
  title: string;
  level: number;
  indent: number;
  /** Root of a branch — styled as a heading, but selectable like any other. */
  isTopLevel: boolean;
}

/**
 * Multi-select picker over the prod-category tree. Implements
 * ControlValueAccessor: the value is the list of selected category ids
 * (`number[]`), which the form posts as `categories[]`. The tree comes from
 * `/formdata/` as a flat, depth-first list with a `level`.
 *
 * Every node is selectable, including the roots of the tree ("games", "misc"…):
 * they are ordinary categories a production can belong to. Roots are only styled
 * as headings and their children are indented under them. A search box filters
 * the list (keeping ancestors of matches visible) and a running count of selected
 * categories is shown below.
 */
@Component({
  selector: 'zx-category-tree-select',
  standalone: true,
  imports: [CommonModule, FormsModule, TranslateModule, SvgIconComponent, ZxCheckboxComponent, ZxChipsComponent],
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
export class ZxCategoryTreeSelectComponent implements ControlValueAccessor, OnInit {
  @Input()
  set nodes(value: CategoryTreeNode[]) {
    const minLevel = value.length ? Math.min(...value.map(n => n.level)) : 0;
    this.allRows = value.map(node => {
      const relativeLevel = node.level - minLevel;
      return {
        id: node.id,
        title: node.title,
        level: relativeLevel,
        indent: relativeLevel,
        isTopLevel: relativeLevel === 0,
      };
    });
    this.rebuild();
  }

  @Input() searchPlaceholder = '';

  query = '';
  disabled = false;
  rows: CategoryRow[] = [];
  chips: ChipItem[] = [];
  selectedCount = 0;

  private allRows: CategoryRow[] = [];
  private selected = new Set<number>();

  private onChange: (value: number[]) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  constructor(
    private readonly cdr: ChangeDetectorRef,
    private readonly iconRegistry: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconRegistry.loadSvg(`${environment.svgUrl}search.svg`, 'search')?.subscribe();
  }

  isSelected(id: number): boolean {
    return this.selected.has(id);
  }

  onChipRemoved(chip: ChipItem): void {
    this.deselect(Number(chip.id));
  }

  deselect(id: number): void {
    if (this.disabled) {
      return;
    }
    this.selected.delete(id);
    this.onChange([...this.selected]);
    this.onTouched();
    this.rebuild();
  }

  onQuery(): void {
    this.rebuild();
  }

  onToggle(id: number, checked: boolean): void {
    if (checked) {
      this.selected.add(id);
    } else {
      this.selected.delete(id);
    }
    this.onChange([...this.selected]);
    this.onTouched();
    this.rebuild();
  }

  writeValue(value: number[] | null): void {
    this.selected = new Set(value ?? []);
    this.rebuild();
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

  private rebuild(): void {
    this.selectedCount = this.selected.size;
    this.chips = this.allRows
      .filter(row => this.selected.has(row.id))
      .map(row => ({id: row.id, title: row.title}));

    const query = this.query.trim().toLowerCase();
    if (!query) {
      this.rows = this.allRows;
      this.cdr.markForCheck();
      return;
    }

    // Keep matching rows and all of their ancestors (nearest lower-level rows above).
    const keep = new Set<number>();
    const stack: CategoryRow[] = [];
    for (const row of this.allRows) {
      while (stack.length && stack[stack.length - 1].level >= row.level) {
        stack.pop();
      }
      if (row.title.toLowerCase().includes(query)) {
        keep.add(row.id);
        for (const ancestor of stack) {
          keep.add(ancestor.id);
        }
      }
      stack.push(row);
    }
    this.rows = this.allRows.filter(row => keep.has(row.id));
    this.cdr.markForCheck();
  }
}
