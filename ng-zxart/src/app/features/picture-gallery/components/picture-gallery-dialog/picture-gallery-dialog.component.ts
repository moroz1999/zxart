import {
  ChangeDetectionStrategy,
  Component,
  computed,
  ElementRef,
  HostListener,
  Inject,
  OnInit,
  QueryList,
  signal,
  ViewChildren,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {PictureGalleryDialogData} from '../../models/picture-gallery-dialog-data';
import {PictureGalleryItem} from '../../models/picture-gallery-item';
import {PictureGallerySlide} from '../../models/picture-gallery-slide';
import {toGallerySlide} from '../../services/picture-gallery.mapper';
import {PictureGallerySlideComponent} from '../picture-gallery-slide/picture-gallery-slide.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';

/** Horizontal travel of a touch that turns the picture, in CSS pixels. */
const SWIPE_DISTANCE = 50;

/**
 * The fullscreen gallery: one image at a time over the whole viewport, turned
 * with the arrows, the arrow keys, a swipe or the thumbnails below.
 */
@Component({
  selector: 'zx-picture-gallery-dialog',
  standalone: true,
  imports: [CommonModule, TranslateModule, SvgIconComponent, ZxButtonComponent, PictureGallerySlideComponent],
  templateUrl: './picture-gallery-dialog.component.html',
  styleUrls: ['./picture-gallery-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureGalleryDialogComponent implements OnInit {
  readonly items: readonly PictureGalleryItem[];
  readonly index = signal(0);
  readonly slide = computed<PictureGallerySlide>(() => toGallerySlide(this.items[this.index()]));
  readonly hasPrevious = computed(() => this.index() > 0);
  readonly hasNext = computed(() => this.index() < this.items.length - 1);

  @ViewChildren('thumb') private thumbs?: QueryList<ElementRef<HTMLElement>>;

  private swipeStart: {x: number; y: number} | null = null;

  constructor(
    @Inject(DIALOG_DATA) data: PictureGalleryDialogData,
    private readonly dialogRef: DialogRef,
    private readonly iconRegistry: SvgIconRegistryService,
  ) {
    this.items = data.items;
    this.index.set(Math.min(Math.max(data.startIndex, 0), data.items.length - 1));
  }

  ngOnInit(): void {
    this.iconRegistry.loadSvg(`${environment.svgUrl}close.svg`, 'close')?.subscribe();
    this.preloadNeighbours();
  }

  show(index: number): void {
    if (index < 0 || index >= this.items.length || index === this.index()) {
      return;
    }
    this.index.set(index);
    this.preloadNeighbours();
    requestAnimationFrame(() => {
      this.thumbs?.get(index)?.nativeElement.scrollIntoView({block: 'nearest', inline: 'nearest'});
    });
  }

  showPrevious(): void {
    this.show(this.index() - 1);
  }

  showNext(): void {
    this.show(this.index() + 1);
  }

  close(): void {
    this.dialogRef.close();
  }

  @HostListener('document:keydown', ['$event'])
  onKeydown(event: KeyboardEvent): void {
    if (event.key === 'ArrowLeft') {
      this.showPrevious();
    } else if (event.key === 'ArrowRight') {
      this.showNext();
    }
  }

  onPointerDown(event: PointerEvent): void {
    this.swipeStart = event.pointerType === 'touch' ? {x: event.clientX, y: event.clientY} : null;
  }

  onPointerUp(event: PointerEvent): void {
    if (this.swipeStart === null) {
      return;
    }
    const deltaX = event.clientX - this.swipeStart.x;
    const deltaY = event.clientY - this.swipeStart.y;
    this.swipeStart = null;
    if (Math.abs(deltaX) < SWIPE_DISTANCE || Math.abs(deltaX) < Math.abs(deltaY)) {
      return;
    }
    if (deltaX < 0) {
      this.showNext();
    } else {
      this.showPrevious();
    }
  }

  trackByIndex(index: number): number {
    return index;
  }

  /** The images either side load ahead, so turning shows them at once. */
  private preloadNeighbours(): void {
    for (const index of [this.index() - 1, this.index() + 1]) {
      const item = this.items[index];
      if (item !== undefined) {
        new Image().src = toGallerySlide(item).src;
      }
    }
  }
}
