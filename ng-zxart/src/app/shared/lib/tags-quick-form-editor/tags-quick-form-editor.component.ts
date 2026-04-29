import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {TagItem} from '../../models/tag-item';
import {ZxButtonComponent} from '../../ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../ui/zx-button-controls/zx-button-controls.component';
import {ZxStackComponent} from '../../ui/zx-stack/zx-stack.component';
import {ZxTagsInputComponent} from '../../ui/zx-tags-input/zx-tags-input.component';

@Component({
  selector: 'zx-tags-quick-form-editor',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxStackComponent,
    ZxTagsInputComponent,
  ],
  templateUrl: './tags-quick-form-editor.component.html',
  styleUrl: './tags-quick-form-editor.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TagsQuickFormEditorComponent {
  @Input() set selectedTags(value: TagItem[]) {
    this.selectedTagsState = [...value];
  }

  @Input() suggestedTags: TagItem[] = [];
  @Input() searchResults: TagItem[] = [];
  @Input() searchLoading = false;
  @Input() disabled = false;
  @Input() saving = false;
  @Input() errorMessage = '';
  @Output() searchQueryChanged = new EventEmitter<string>();
  @Output() selectedTagsChanged = new EventEmitter<TagItem[]>();
  @Output() saveRequested = new EventEmitter<TagItem[]>();

  private selectedTagsState: TagItem[] = [];

  get selectedTagsValue(): TagItem[] {
    return this.selectedTagsState;
  }

  get visibleSuggestedTags(): TagItem[] {
    return this.excludeSelectedTags(this.suggestedTags);
  }

  get visibleSearchResults(): TagItem[] {
    return this.excludeSelectedTags(this.searchResults);
  }

  onTagSelected(tag: TagItem): void {
    if (this.hasTag(tag.title)) {
      return;
    }

    this.selectedTagsState = [...this.selectedTagsState, tag];
    this.emitSelectedTags();
  }

  onCustomTagAdded(title: string): void {
    if (this.hasTag(title)) {
      return;
    }

    this.selectedTagsState = [
      ...this.selectedTagsState,
      {id: null, title, description: null},
    ];
    this.emitSelectedTags();
  }

  onTagRemoved(tag: TagItem): void {
    const normalizedTitle = this.normalizeTagTitle(tag.title);
    this.selectedTagsState = this.selectedTagsState.filter(
      selectedTag => this.normalizeTagTitle(selectedTag.title) !== normalizedTitle,
    );
    this.emitSelectedTags();
  }

  onSuggestedTagClick(tag: TagItem): void {
    this.onTagSelected(tag);
  }

  onSaveClick(): void {
    this.saveRequested.emit([...this.selectedTagsState]);
  }

  private excludeSelectedTags(tags: TagItem[]): TagItem[] {
    return tags.filter(tag => this.hasTag(tag.title) === false);
  }

  private hasTag(title: string): boolean {
    const normalizedTitle = this.normalizeTagTitle(title);
    return this.selectedTagsState.some(tag => this.normalizeTagTitle(tag.title) === normalizedTitle);
  }

  private normalizeTagTitle(title: string): string {
    return title.trim().toLocaleLowerCase();
  }

  private emitSelectedTags(): void {
    this.selectedTagsChanged.emit([...this.selectedTagsState]);
  }
}
