import {AfterViewInit, Component, TemplateRef, ViewChild} from '@angular/core';
import {PictureGalleryService} from '../../services/picture-gallery.service';
import {
  PictureGalleryZoomOverlayComponent
} from '../picture-gallery-zoom-overlay/picture-gallery-zoom-overlay.component';

@Component({
  selector: 'zx-picture-gallery-host',
  standalone: true,
  imports: [PictureGalleryZoomOverlayComponent],
  templateUrl: './picture-gallery-host.component.html',
  styleUrls: ['./picture-gallery-host.component.scss']
})
export class PictureGalleryHostComponent implements AfterViewInit {
  @ViewChild('galleryImageTemplate', {static: true}) private galleryImageTemplate?: TemplateRef<unknown>;

  constructor(private galleryService: PictureGalleryService) {}

  ngAfterViewInit(): void {
    if (this.galleryImageTemplate) {
      this.galleryService.registerImageTemplate(this.galleryImageTemplate);
    }
  }
}
