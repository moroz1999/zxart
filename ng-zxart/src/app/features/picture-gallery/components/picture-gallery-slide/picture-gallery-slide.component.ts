import {
  AfterViewInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  EventEmitter,
  Input,
  OnChanges,
  OnDestroy,
  OnInit,
  Output,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {PictureGallerySlide} from '../../models/picture-gallery-slide';
import {integerFitScale, PixelSize} from '../../utils/integer-fit-scale';
import {PictureGalleryInfoComponent} from '../picture-gallery-info/picture-gallery-info.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPixelZoomComponent} from '../../../../shared/ui/zx-pixel-zoom/zx-pixel-zoom.component';

/** Space kept free between the picture and the edges of its area, in CSS pixels. */
const STAGE_MARGIN = 16;

/**
 * One image of the fullscreen gallery: the picture block, where the image is
 * shown at a whole multiple of its native size with the loupe and the arrows
 * beside it, and — for pictures — their info in a block of its own, beside the
 * picture on wide screens and below it on narrow ones.
 */
@Component({
  selector: 'zx-picture-gallery-slide',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxPixelZoomComponent,
    PictureGalleryInfoComponent,
  ],
  templateUrl: './picture-gallery-slide.component.html',
  styleUrls: ['./picture-gallery-slide.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureGallerySlideComponent implements OnInit, OnChanges, AfterViewInit, OnDestroy {
  @Input({required: true}) slide!: PictureGallerySlide;
  @Input() hasPrevious = false;
  @Input() hasNext = false;
  @Output() readonly previous = new EventEmitter<void>();
  @Output() readonly next = new EventEmitter<void>();

  /** Area the picture is fitted into: the picture block less the arrows' margins. */
  @ViewChild('canvas', {static: true}) private canvas!: ElementRef<HTMLElement>;

  /** Displayed size; null until the native size and the area are known. */
  displaySize: PixelSize | null = null;

  private nativeSize: PixelSize | null = null;
  private resizeObserver?: ResizeObserver;

  constructor(
    private readonly cdr: ChangeDetectorRef,
    private readonly iconRegistry: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconRegistry.loadSvg(`${environment.svgUrl}chevron-left.svg`, 'chevron-left')?.subscribe();
    this.iconRegistry.loadSvg(`${environment.svgUrl}chevron-right.svg`, 'chevron-right')?.subscribe();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['slide']) {
      this.measureNativeSize(this.slide.src);
    }
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

  private measureNativeSize(src: string): void {
    this.nativeSize = null;
    this.displaySize = null;
    const image = new Image();
    image.onload = () => {
      // a newer slide may have arrived while this one loaded
      if (this.slide.src !== src) {
        return;
      }
      this.nativeSize = {width: image.naturalWidth, height: image.naturalHeight};
      this.updateDisplaySize();
      this.cdr.markForCheck();
    };
    image.src = src;
  }

  private updateDisplaySize(): void {
    if (this.nativeSize === null) {
      return;
    }
    const canvas = this.canvas.nativeElement;
    const scale = integerFitScale(this.nativeSize, {width: canvas.clientWidth, height: canvas.clientHeight}, STAGE_MARGIN);
    this.displaySize = scale > 0
      ? {width: Math.floor(this.nativeSize.width * scale), height: Math.floor(this.nativeSize.height * scale)}
      : null;
  }
}
