import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxButtonComponent} from '../zx-button/zx-button.component';

export interface FileUploadChange {
  file: File | null;
  removed: boolean;
}

/**
 * Single non-image file field for forms: shows the current filename (if any),
 * lets the user pick a replacement or remove the existing file. Emits the chosen
 * `File` / removal so the host form can include it in a multipart submit. The
 * image counterpart is {@link ZxImageUploadComponent}.
 */
@Component({
  selector: 'zx-file-upload',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxButtonComponent],
  templateUrl: './zx-file-upload.component.html',
  styleUrl: './zx-file-upload.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFileUploadComponent {
  /** Name of the currently stored file, or null when there is none. */
  @Input() fileName: string | null = null;
  @Input() accept = '';
  @Output() readonly changed = new EventEmitter<FileUploadChange>();

  pickedName: string | null = null;
  private removed = false;

  get displayName(): string | null {
    if (this.pickedName) {
      return this.pickedName;
    }
    return this.fileName && !this.removed ? this.fileName : null;
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (!file) {
      return;
    }
    this.removed = false;
    this.pickedName = file.name;
    this.changed.emit({file, removed: false});
  }

  onRemove(): void {
    this.pickedName = null;
    this.removed = true;
    this.changed.emit({file: null, removed: true});
  }
}
