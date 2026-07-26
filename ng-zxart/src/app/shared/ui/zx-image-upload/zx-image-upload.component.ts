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
 * pick a new one (with preview) or remove the existing one. Emits the chosen
 * `File` / removal so the host form can include it in a multipart submit.
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
  @Output() readonly changed = new EventEmitter<ImageUploadChange>();

  previewUrl: string | null = null;
  private removed = false;

  get displayUrl(): string | null {
    if (this.previewUrl) {
      return this.previewUrl;
    }
    return this.imageUrl && !this.removed ? this.imageUrl : null;
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (!file) {
      return;
    }
    this.removed = false;
    this.revokePreview();
    this.previewUrl = URL.createObjectURL(file);
    this.changed.emit({file, removed: false});
  }

  onRemove(): void {
    this.revokePreview();
    this.previewUrl = null;
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
