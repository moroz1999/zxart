import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {LightboxModule} from 'ng-gallery/lightbox';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ProdFileDto} from '../../../prod-details/models/prod-file.dto';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {ZxReleaseSectionHeadComponent} from '../zx-release-section-head/zx-release-section-head.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-release-screenshots-section',
  standalone: true,
  imports: [
    CommonModule,
    LightboxModule,
    TranslateModule,
    ZxButtonComponent,
    SvgIconComponent,
    PictureGalleryHostComponent,
    ZxReleaseSectionHeadComponent,
  ],
  templateUrl: './zx-release-screenshots-section.component.html',
  styleUrl: './zx-release-screenshots-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseScreenshotsSectionComponent implements OnInit {
  @Input({required: true}) screenshots!: ProdFileDto[];

  galleryId = '';

  constructor(
    private readonly gallery: PictureGalleryService,
    private readonly iconReg: SvgIconRegistryService,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }

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
