import {
  AfterViewChecked,
  Component,
  ElementRef,
  EventEmitter,
  HostListener,
  Input,
  Output,
  ViewChild
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {ZxBodyDirective, ZxCaptionDirective} from '../../directives/typography/typography.directives';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxCheckboxFieldComponent} from '../zx-checkbox-field/zx-checkbox-field.component';
import {ZxInputComponent} from '../zx-input/zx-input.component';

export interface ZxFilterPickerItem {
  id: string;
  label: string;
}

@Component({
  selector: 'zx-filter-picker',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxBodyDirective,
    ZxCaptionDirective,
    ZxCheckboxFieldComponent,
    ZxInputComponent,
  ],
  templateUrl: './zx-filter-picker.component.html',
  styleUrl: './zx-filter-picker.component.scss',
})
export class ZxFilterPickerComponent implements AfterViewChecked {
  @Input() label = '';
  @Input() items: ZxFilterPickerItem[] = [];
  @Input() selectedIds: string[] = [];
  @Input() placeholder = '';
  @Input() searchEnabled = false;
  @Input() multi = true;
  @Output() selectedIdsChange = new EventEmitter<string[]>();

  @ViewChild('popoverEl') popoverEl?: ElementRef<HTMLElement>;
  @ViewChild(ZxInputComponent) searchInput?: ZxInputComponent;

  searchTerm = '';
  popoverOpen = false;
  dropUp = false;
  private needsFocus = false;

  constructor(private elementRef: ElementRef<HTMLElement>) {}

  ngAfterViewChecked(): void {
    if (this.popoverOpen && this.popoverEl) {
      this.updateDropDirection();
    }
    if (this.needsFocus && this.searchInput?.nativeElement) {
      this.searchInput.nativeElement.focus();
      this.needsFocus = false;
    }
  }

  private updateDropDirection(): void {
    const hostRect = this.elementRef.nativeElement.getBoundingClientRect();
    const popoverHeight = this.popoverEl!.nativeElement.offsetHeight;
    const spaceBelow = window.innerHeight - hostRect.bottom;
    const spaceAbove = hostRect.top;
    this.dropUp = spaceBelow < popoverHeight && spaceAbove > spaceBelow;
  }

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

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.popoverOpen = !this.popoverOpen;
    if (this.popoverOpen && this.searchEnabled) {
      this.needsFocus = true;
    }
  }

  closePopover(): void {
    this.popoverOpen = false;
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

  @HostListener('document:click', ['$event'])
  onDocumentClick(event: Event): void {
    if (!this.popoverOpen) {
      return;
    }
    const target = event.target as Node | null;
    if (target && this.elementRef.nativeElement.contains(target)) {
      return;
    }
    this.closePopover();
  }

  @HostListener('document:keydown.escape', ['$event'])
  onEscape(event: KeyboardEvent): void {
    if (this.popoverOpen) {
      event.stopPropagation();
      this.closePopover();
    }
  }

  trackById(_: number, item: ZxFilterPickerItem): string {
    return item.id;
  }
}
