import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxScreenshotGridSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-screenshot-grid-skeleton/zx-screenshot-grid-skeleton.component';
import {
  PictureGalleryHostComponent,
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';
import {LightboxModule} from 'ng-gallery/lightbox';
import {ProdScreenshotsApiService} from '../../services/prod-screenshots-api.service';
import {ProdFileDto} from '../../models/prod-file.dto';
import {HeadingDirective, TextDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-prod-screenshots-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxScreenshotGridSkeletonComponent,
    PictureGalleryHostComponent,
    LightboxModule,
    TextDirective,
    HeadingDirective,
    ZxStackComponent,
  ],
  templateUrl: './zx-prod-screenshots-section.component.html',
  styleUrls: ['./zx-prod-screenshots-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdScreenshotsSectionComponent {
  @Input({required: true}) elementId!: number;
  @Input() titleKey = 'prod-details.screenshots';

  readonly maxVisible = 6;
  loading = false;
  loaded = false;
  files: ProdFileDto[] = [];
  galleryId = '';

  get displayedFiles(): ProdFileDto[] {
    return this.files.length > this.maxVisible
      ? this.files.slice(0, this.maxVisible - 1)
      : this.files;
  }

  get moreCount(): number {
    return Math.max(0, this.files.length - this.maxVisible + 1);
  }

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.files.length === 0 ? 'none' : '';
  }

  constructor(
    private readonly api: ProdScreenshotsApiService,
    private readonly gallery: PictureGalleryService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.galleryId = `zx-prod-screenshots-${this.elementId}`;
    this.loading = true;
    this.api.getScreenshots(this.elementId).subscribe(files => {
      this.files = files;
      this.loaded = true;
      this.loading = false;
      if (files.length) {
        this.gallery.loadItems(this.galleryId, files.map(this.toGalleryItem));
      }
      this.cdr.markForCheck();
    });
  }

  private toGalleryItem(file: ProdFileDto): PictureGalleryItem {
    return {
      id: file.id,
      title: file.title,
      thumbUrl: file.imageUrl ?? file.fullImageUrl ?? '',
      largeUrl: file.fullImageUrl ?? file.imageUrl ?? '',
      detailsUrl: file.downloadUrl,
    };
  }
}
