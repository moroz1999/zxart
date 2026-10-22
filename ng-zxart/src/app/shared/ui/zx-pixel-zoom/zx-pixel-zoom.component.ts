import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {NgxImageZoomModule} from 'ngx-image-zoom';

/**
 * A pixel-art image drawn at an exact size, with a hover loupe: a region under
 * the pointer is magnified, and the wheel changes the magnification. The loupe
 * library measures the image once, so it is rebuilt whenever the size or the
 * image changes.
 */
@Component({
  selector: 'zx-pixel-zoom',
  standalone: true,
  imports: [NgxImageZoomModule],
  templateUrl: './zx-pixel-zoom.component.html',
  styleUrl: './zx-pixel-zoom.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPixelZoomComponent {
  /** Image drawn at `width` × `height`. */
  @Input({required: true}) image = '';
  /** Larger render the loupe magnifies; the image itself when absent. */
  @Input() fullImage = '';
  @Input({required: true}) width = 0;
  @Input({required: true}) height = 0;
  @Input() alt = '';
  @Input() magnification = 3;
  @Input() lensWidth = 200;
  @Input() lensHeight = 150;

  @HostBinding('style.--zx-pixel-zoom-width.px')
  get widthVariable(): number {
    return this.width;
  }

  @HostBinding('style.--zx-pixel-zoom-height.px')
  get heightVariable(): number {
    return this.height;
  }

  /** One loupe per size and image; empty until the size is known. */
  get instances(): string[] {
    return this.image !== '' && this.width > 0 ? [`${this.width}x${this.height}|${this.image}`] : [];
  }
}
