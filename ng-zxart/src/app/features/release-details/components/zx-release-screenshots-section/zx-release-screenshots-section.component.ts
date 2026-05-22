import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {LightboxModule} from 'ng-gallery/lightbox';
import {ProdFileDto} from '../../../prod-details/models/prod-file.dto';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';

@Component({
  selector: 'zx-release-screenshots-section',
  standalone: true,
  imports: [
    CommonModule,
    LightboxModule,
    HeadingDirective,
    TextDirective,
    ZxInlineComponent,
    PictureGalleryHostComponent,
  ],
  templateUrl: './zx-release-screenshots-section.component.html',
  styleUrl: './zx-release-screenshots-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseScreenshotsSectionComponent implements OnInit {
  @Input({required: true}) screenshots!: ProdFileDto[];

  galleryId = '';

  constructor(private readonly gallery: PictureGalleryService) {}

  ngOnInit(): void {
    if (this.screenshots.length) {
      this.galleryId = `release-screenshots-${this.screenshots[0].id}`;
      this.gallery.loadItems(this.galleryId, this.screenshots.map(f => ({
        id: f.id,
        title: f.title,
        thumbUrl: f.imageUrl ?? f.fullImageUrl ?? '',
        largeUrl: f.fullImageUrl ?? f.imageUrl ?? '',
        detailsUrl: f.downloadUrl,
      })));
    }
  }

  trackById(_: number, file: ProdFileDto): number {
    return file.id;
  }
}
