import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {TagChipItem} from '../../models/tag-chip-item';
import {TagItem} from '../../models/tag-item';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxSpinnerComponent} from '../zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../zx-stack/zx-stack.component';
import {ZxTagsChipsComponent} from '../zx-tags-chips/zx-tags-chips.component';

@Component({
  selector: 'zx-tags-input',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxButtonComponent,
    ZxInputComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    ZxTagsChipsComponent,
  ],
  templateUrl: './zx-tags-input.component.html',
  styleUrl: './zx-tags-input.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTagsInputComponent {
  @Input() tags: TagItem[] = [];
  @Input() searchResults: TagItem[] = [];
  @Input() placeholder = '';
  @Input() disabled = false;
  @Input() searchLoading = false;
  @Input() removeButtonAriaLabel = '';
  @Output() queryChanged = new EventEmitter<string>();
  @Output() tagSelected = new EventEmitter<TagItem>();
  @Output() customTagAdded = new EventEmitter<string>();
  @Output() tagRemoved = new EventEmitter<TagItem>();

  query = '';

  readonly positions: ConnectedPosition[] = [
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
  ];

  get dropdownOpen(): boolean {
    return this.disabled !== true && this.query.trim() !== '' && (this.searchLoading || this.searchResults.length > 0);
  }

  onQueryChange(value: string): void {
    this.query = value;
    this.queryChanged.emit(value);
  }

  onKeyup(event: KeyboardEvent): void {
    if (event.key !== 'Enter') {
      return;
    }

    event.preventDefault();
    this.addCustomTag();
  }

  onSelectSearchResult(tag: TagItem): void {
    this.tagSelected.emit(tag);
    this.clearQuery();
  }

  onRemoveTag(tag: TagChipItem): void {
    this.tagRemoved.emit(tag as TagItem);
  }

  addCustomTag(): void {
    const normalizedQuery = this.query.trim();
    if (normalizedQuery === '') {
      return;
    }

    this.customTagAdded.emit(normalizedQuery);
    this.clearQuery();
  }

  closeDropdown(): void {
    this.clearQuery();
  }

  private clearQuery(): void {
    this.query = '';
    this.queryChanged.emit('');
  }
}
