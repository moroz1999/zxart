import {Directive, HostBinding, HostListener, inject, Input} from '@angular/core';
import {GALLERY_OPENER} from '../models/gallery-opener';

/** Opens the gallery `zxGalleryId` at image `zxGalleryOpen` when the host is clicked. */
@Directive({
  selector: '[zxGalleryOpen]',
  standalone: true,
})
export class ZxGalleryOpenDirective {
  @Input({required: true}) zxGalleryOpen!: number;
  @Input({required: true}) zxGalleryId!: string;

  @HostBinding('style.cursor') readonly cursor = 'zoom-in';

  private readonly opener = inject(GALLERY_OPENER);

  @HostListener('click')
  open(): void {
    void this.opener.open(this.zxGalleryId, this.zxGalleryOpen);
  }
}
