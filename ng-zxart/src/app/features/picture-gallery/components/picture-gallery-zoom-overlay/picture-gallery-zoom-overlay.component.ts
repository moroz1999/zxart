import {AfterViewInit, Component, ElementRef, Input, OnChanges, OnDestroy, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatIconModule} from '@angular/material/icon';
import {NgxImageZoomModule} from 'ngx-image-zoom';
import {PictureGalleryService} from '../../services/picture-gallery.service';

@Component({
  selector: 'zx-picture-gallery-zoom-overlay',
  standalone: true,
  imports: [CommonModule, MatIconModule, NgxImageZoomModule],
  templateUrl: './picture-gallery-zoom-overlay.component.html',
  styleUrls: ['./picture-gallery-zoom-overlay.component.scss']
})
export class PictureGalleryZoomOverlayComponent implements AfterViewInit, OnChanges, OnDestroy {
  @Input() src = '';
  @Input() thumb = '';
  @Input() active = false;

  canZoom = false;

  private naturalWidth = 0;
  private naturalHeight = 0;
  private resizeObserver?: ResizeObserver;

  constructor(
    private host: ElementRef<HTMLElement>,
    public galleryService: PictureGalleryService,
  ) {}

  ngAfterViewInit(): void {
    this.resizeObserver = new ResizeObserver(() => this.updateCanZoom());
    this.resizeObserver.observe(this.host.nativeElement);
    this.preloadImage();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['src']) {
      this.preloadImage();
    }
    if (changes['active']) {
      this.updateCanZoom();
    }
  }

  ngOnDestroy(): void {
    this.resizeObserver?.disconnect();
  }

  toggleZoom(event: MouseEvent): void {
    event.preventDefault();
    event.stopPropagation();
    this.galleryService.toggleZoom();
  }

  private preloadImage(): void {
    if (!this.src) {
      this.naturalWidth = 0;
      this.naturalHeight = 0;
      this.canZoom = false;
      return;
    }
    const image = new Image();
    image.onload = () => {
      this.naturalWidth = image.naturalWidth;
      this.naturalHeight = image.naturalHeight;
      this.updateCanZoom();
    };
    image.src = this.src;
  }

  private updateCanZoom(): void {
    if (!this.active || !this.naturalWidth || !this.naturalHeight) {
      this.canZoom = false;
      return;
    }
    const rect = this.host.nativeElement.getBoundingClientRect();
    const viewWidth = Math.floor(rect.width);
    const viewHeight = Math.floor(rect.height);
    this.canZoom = this.naturalWidth > viewWidth || this.naturalHeight > viewHeight;
  }
}
