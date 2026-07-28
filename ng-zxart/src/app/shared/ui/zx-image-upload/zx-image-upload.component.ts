import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnDestroy, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxButtonComponent} from '../zx-button/zx-button.component';

export interface ImageUploadChange {
  file: File | null;
  removed: boolean;
}

/**
 * Single image field for forms: shows the current image (if any), lets the user
 * pick a new one or remove the existing one. Emits the chosen `File` / removal
 * so the host form can include it in a multipart submit. A picked file is
 * previewed only when the browser can render it; otherwise its name is shown.
 */
@Component({
  selector: 'zx-image-upload',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxButtonComponent],
  templateUrl: './zx-image-upload.component.html',
  styleUrl: './zx-image-upload.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxImageUploadComponent implements OnDestroy {
  /** URL of the currently stored image, or null when there is none. */
  @Input() imageUrl: string | null = null;
  /** `accept` of the picker; narrows it to the formats the field stores. */
  @Input() accept = 'image/*';
  @Output() readonly changed = new EventEmitter<ImageUploadChange>();

  previewUrl: string | null = null;
  pickedName: string | null = null;
  private removed = false;

  get displayUrl(): string | null {
    if (this.previewUrl) {
      return this.previewUrl;
    }
    // a picked file without a preview still replaces the stored image
    if (this.pickedName !== null) {
      return null;
    }
    return this.imageUrl && !this.removed ? this.imageUrl : null;
  }

  /** Name of a picked file that has no thumbnail of its own. */
  get displayName(): string | null {
    return this.previewUrl ? null : this.pickedName;
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (!file) {
      return;
    }
    this.removed = false;
    this.revokePreview();
    this.pickedName = file.name;
    // native ZX Spectrum screens carry no browser-renderable type, so they get
    // no preview and the file name is the only feedback until the picture is
    // stored and converted
    if (file.type.startsWith('image/')) {
      this.previewUrl = URL.createObjectURL(file);
    }
    this.changed.emit({file, removed: false});
  }

  onRemove(): void {
    this.revokePreview();
    this.previewUrl = null;
    this.pickedName = null;
    this.removed = true;
    this.changed.emit({file: null, removed: true});
  }

  ngOnDestroy(): void {
    this.revokePreview();
  }

  private revokePreview(): void {
    if (this.previewUrl) {
      URL.revokeObjectURL(this.previewUrl);
      this.previewUrl = null;
    }
  }
}
