import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, forwardRef, Input, OnInit} from '@angular/core';
import {ControlValueAccessor, FormsModule, NG_VALUE_ACCESSOR} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {ChipItem} from '../../models/chip-item';
import {ZxCheckboxComponent} from '../zx-checkbox/zx-checkbox.component';
import {ZxChipsComponent} from '../zx-chips/zx-chips.component';
import {ZxSelectOption} from '../zx-select/zx-select.component';
import {MultiSelectGroup} from './zx-multi-select-filter.models';

interface RenderGroup {
  label: string;
  options: ZxSelectOption[];
}

/**
 * Universal multi-select control with a search filter and a scrollable checkbox
 * list. Selected values are shown as removable chips above the filter, with a
 * running count below. Implements ControlValueAccessor over `string[]`.
 *
 * Provide flat `options` for a single list (e.g. languages, release formats) or
 * `groups` for a grouped list with uppercase group headers (e.g. hardware).
 *
 * Selected values render as removable {@link ZxChipsComponent} chips (keyed by
 * the option value via each chip's `id`).
 */
@Component({
  selector: 'zx-multi-select-filter',
  standalone: true,
  imports: [CommonModule, FormsModule, TranslateModule, SvgIconComponent, ZxCheckboxComponent, ZxChipsComponent],
  templateUrl: './zx-multi-select-filter.component.html',
  styleUrl: './zx-multi-select-filter.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      useExisting: forwardRef(() => ZxMultiSelectFilterComponent),
      multi: true,
    },
  ],
})
export class ZxMultiSelectFilterComponent implements ControlValueAccessor, OnInit {
  @Input() searchPlaceholder = '';

  @Input()
  set options(value: ZxSelectOption[]) {
    this.sourceGroups = [{label: '', options: value ?? []}];
    this.rebuild();
  }

  @Input()
  set groups(value: MultiSelectGroup[]) {
    this.sourceGroups = value ?? [];
    this.rebuild();
  }

  query = '';
  disabled = false;
  renderGroups: RenderGroup[] = [];
  chips: ChipItem[] = [];
  selectedCount = 0;

  private sourceGroups: MultiSelectGroup[] = [];
  private selected = new Set<string>();
  private readonly labels = new Map<string, string>();

  private onChange: (value: string[]) => void = () => undefined;
  private onTouched: () => void = () => undefined;

  constructor(
    private readonly cdr: ChangeDetectorRef,
    private readonly iconRegistry: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconRegistry.loadSvg(`${environment.svgUrl}search.svg`, 'search')?.subscribe();
  }

  get hasResults(): boolean {
    return this.renderGroups.length > 0;
  }

  isSelected(value: string): boolean {
    return this.selected.has(value);
  }

  onQuery(): void {
    this.rebuild();
  }

  toggle(value: string, checked: boolean): void {
    if (checked) {
      this.selected.add(value);
    } else {
      this.selected.delete(value);
    }
    this.emit();
  }

  onChipRemoved(chip: ChipItem): void {
    this.deselect(String(chip.id));
  }

  deselect(value: string): void {
    if (this.disabled) {
      return;
    }
    this.selected.delete(value);
    this.emit();
  }

  writeValue(value: string[] | null): void {
    this.selected = new Set(value ?? []);
    this.rebuild();
  }

  registerOnChange(fn: (value: string[]) => void): void {
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
    this.onChange([...this.selected]);
    this.onTouched();
    this.rebuild();
  }

  private rebuild(): void {
    this.labels.clear();
    for (const group of this.sourceGroups) {
      for (const option of group.options) {
        this.labels.set(option.value, option.label);
      }
    }

    this.chips = [...this.selected].map(value => ({id: value, title: this.labels.get(value) ?? value}));
    this.selectedCount = this.selected.size;

    const query = this.query.trim().toLowerCase();
    this.renderGroups = this.sourceGroups
      .map(group => ({
        label: group.label,
        options: query
          ? group.options.filter(option => option.label.toLowerCase().includes(query))
          : group.options,
      }))
      .filter(group => group.options.length > 0);

    this.cdr.markForCheck();
  }
}
