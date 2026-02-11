import {Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatButtonModule} from '@angular/material/button';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatIconModule} from '@angular/material/icon';
import {MatInputModule} from '@angular/material/input';
import {MatMenuModule} from '@angular/material/menu';
import {TranslateModule} from '@ngx-translate/core';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxBodyDirective, ZxCaptionDirective} from '../../directives/typography/typography.directives';

export interface ZxFilterPickerItem {
  id: string;
  label: string;
}

@Component({
  selector: 'zx-filter-picker',
  standalone: true,
  imports: [
    CommonModule,
    MatButtonModule,
    MatCheckboxModule,
    MatFormFieldModule,
    MatIconModule,
    MatInputModule,
    MatMenuModule,
    TranslateModule,
    ZxButtonComponent,
    ZxBodyDirective,
    ZxCaptionDirective,
  ],
  templateUrl: './zx-filter-picker.component.html',
  styleUrl: './zx-filter-picker.component.scss',
})
export class ZxFilterPickerComponent {
  @Input() label = '';
  @Input() items: ZxFilterPickerItem[] = [];
  @Input() selectedIds: string[] = [];
  @Input() placeholder = '';
  @Input() searchEnabled = false;
  @Input() multi = true;
  @Output() selectedIdsChange = new EventEmitter<string[]>();

  searchTerm = '';
  menuOpen = false;

  get hasSelection(): boolean {
    return this.selectedIds.length > 0;
  }

  get summaryText(): string {
    const selectedCount = this.selectedIds.length;
    if (selectedCount === 0) {
      return this.placeholder || this.label;
    }

    const selectedLabels = this.items
      .filter(item => this.selectedIds.includes(item.id))
      .map(item => item.label);

    const labelAt = (index: number): string => selectedLabels[index] ?? this.selectedIds[index] ?? '';

    if (selectedCount === 1) {
      return labelAt(0);
    }

    if (selectedCount === 2) {
      return `${labelAt(0)}, ${labelAt(1)}`;
    }

    return `${labelAt(0)}, ${labelAt(1)} + ${selectedCount - 2}`;
  }

  get summaryIsPlaceholder(): boolean {
    return !this.hasSelection;
  }

  get filteredItems(): ZxFilterPickerItem[] {
    if (!this.searchEnabled) {
      return this.items;
    }
    const term = this.searchTerm.trim().toLocaleLowerCase();
    if (!term) {
      return this.items;
    }
    return this.items.filter(item => item.label.toLocaleLowerCase().includes(term));
  }

  onSearchChange(event: Event): void {
    const target = event.target as HTMLInputElement | null;
    this.searchTerm = target?.value ?? '';
  }

  toggleSelection(itemId: string, checked: boolean): void {
    const selected = new Set(this.selectedIds);
    if (this.multi) {
      if (checked) {
        selected.add(itemId);
      } else {
        selected.delete(itemId);
      }
    } else {
      selected.clear();
      if (checked) {
        selected.add(itemId);
      }
    }
    this.selectedIdsChange.emit(Array.from(selected));
  }

  clearSelection(event?: Event): void {
    event?.stopPropagation();
    this.selectedIdsChange.emit([]);
  }

  isSelected(itemId: string): boolean {
    return this.selectedIds.includes(itemId);
  }

  onMenuOpened(): void {
    this.menuOpen = true;
  }

  onMenuClosed(): void {
    this.menuOpen = false;
  }

  trackById(_: number, item: ZxFilterPickerItem): string {
    return item.id;
  }
}
