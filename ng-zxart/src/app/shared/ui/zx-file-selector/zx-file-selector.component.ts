import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {FileSelectorItem} from '../../models/form-data-response';
import {ZxButtonComponent} from '../zx-button/zx-button.component';

/**
 * Manager for a multi-file selector (screenshots, inlays, maps, …). Lists the
 * existing files (image thumbnail or filename) with per-file delete, and lets the
 * user stage new files to upload. Existing-file deletion is emitted for the host
 * to run live; staged uploads are read via {@link stagedFiles} on submit.
 */
@Component({
  selector: 'zx-file-selector',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxButtonComponent],
  templateUrl: './zx-file-selector.component.html',
  styleUrl: './zx-file-selector.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFileSelectorComponent {
  @Input() files: FileSelectorItem[] = [];
  @Input() accept = '';
  /** Emits the id of an existing file the user removed (host deletes it live). */
  @Output() readonly removeExisting = new EventEmitter<number>();
  /** Emits the staged new files whenever the selection changes. */
  @Output() readonly filesChange = new EventEmitter<File[]>();

  /** New files staged for upload. */
  stagedFiles: File[] = [];
  removedIds = new Set<number>();

  get visibleFiles(): FileSelectorItem[] {
    return this.files.filter(file => !this.removedIds.has(file.id));
  }

  onFilesSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.stagedFiles = Array.from(input.files ?? []);
    this.filesChange.emit(this.stagedFiles);
  }

  onRemoveExisting(id: number): void {
    this.removedIds.add(id);
    this.removeExisting.emit(id);
  }

  trackById(_index: number, file: FileSelectorItem): number {
    return file.id;
  }

  trackByName(_index: number, file: File): string {
    return file.name;
  }
}
