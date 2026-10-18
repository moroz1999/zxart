import {
  AfterViewInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  HostBinding,
  Input,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {PictureGalleryService} from '../../services/picture-gallery.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {PictureGallerySlideData} from '../../services/picture-gallery.mapper';
import {integerFitScale} from '../../utils/integer-fit-scale';
import {PictureGalleryInfoComponent} from '../picture-gallery-info/picture-gallery-info.component';
import {
  PictureGalleryZoomOverlayComponent
} from '../picture-gallery-zoom-overlay/picture-gallery-zoom-overlay.component';

/** Space kept free between the picture and the edges of its stage, in CSS pixels. */
const STAGE_MARGIN = 16;

/**
 * One picture in the fullscreen gallery: the picture on its stage, shown at a
 * whole multiple of its native size, and its info in a block of its own —
 * beside the stage on wide screens, below it on narrow ones.
 */
@Component({
  selector: 'zx-picture-gallery-slide',
  standalone: true,
  imports: [CommonModule, TranslateModule, SvgIconComponent, ZxButtonComponent, PictureGalleryInfoComponent, PictureGalleryZoomOverlayComponent],
  templateUrl: './picture-gallery-slide.component.html',
  styleUrls: ['./picture-gallery-slide.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureGallerySlideComponent implements OnInit, AfterViewInit, OnDestroy {
  @Input({required: true}) slide!: PictureGallerySlideData;
  @Input() active = false;
  @Input() first = false;
  @Input() last = false;

  /**
   * The slider renders the slides around the shown one too. Their links and
   * buttons stay out of focus: the lightbox focuses its first focusable element
   * on opening, and focus in a neighbouring slide would scroll the slider there
   * and switch the gallery to that picture.
   */
  @HostBinding('attr.inert')
  get inert(): '' | null {
    return this.active ? null : '';
  }

  /** Area the picture is fitted into: the picture block less the arrows' margins. */
  @ViewChild('canvas', {static: true}) private canvas!: ElementRef<HTMLElement>;
  @ViewChild('image', {static: true}) private image!: ElementRef<HTMLImageElement>;

  /** Displayed size; null until the image has loaded and the stage is measured. */
  displaySize: {width: number; height: number} | null = null;

  private resizeObserver?: ResizeObserver;

  constructor(
    private readonly cdr: ChangeDetectorRef,
    private readonly galleryService: PictureGalleryService,
    private readonly iconRegistry: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconRegistry.loadSvg(`${environment.svgUrl}chevron-left.svg`, 'chevron-left')?.subscribe();
    this.iconRegistry.loadSvg(`${environment.svgUrl}chevron-right.svg`, 'chevron-right')?.subscribe();
  }

  showPrevious(): void {
    this.galleryService.showPrevious();
  }

  showNext(): void {
    this.galleryService.showNext();
  }

  ngAfterViewInit(): void {
    this.resizeObserver = new ResizeObserver(() => {
      this.updateDisplaySize();
      this.cdr.markForCheck();
    });
    this.resizeObserver.observe(this.canvas.nativeElement);
  }

  ngOnDestroy(): void {
    this.resizeObserver?.disconnect();
  }

  onImageLoad(): void {
    this.updateDisplaySize();
    this.cdr.markForCheck();
  }

  private updateDisplaySize(): void {
    const image = this.image.nativeElement;
    const native = {width: image.naturalWidth, height: image.naturalHeight};
    const stage = {width: this.canvas.nativeElement.clientWidth, height: this.canvas.nativeElement.clientHeight};
    const scale = integerFitScale(native, stage, STAGE_MARGIN);
    this.displaySize = scale > 0
      ? {width: Math.floor(native.width * scale), height: Math.floor(native.height * scale)}
      : null;
  }
}
