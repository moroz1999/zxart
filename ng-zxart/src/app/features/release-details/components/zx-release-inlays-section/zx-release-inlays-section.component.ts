import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {LightboxModule} from 'ng-gallery/lightbox';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ProdReleaseInlayDto} from '../../../prod-details/models/prod-release-inlay.dto';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {ZxReleaseSectionHeadComponent} from '../zx-release-section-head/zx-release-section-head.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-release-inlays-section',
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
  templateUrl: './zx-release-inlays-section.component.html',
  styleUrl: './zx-release-inlays-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseInlaysSectionComponent implements OnInit {
  @Input({required: true}) inlays!: ProdReleaseInlayDto[];

  galleryId = '';

  constructor(
    private readonly gallery: PictureGalleryService,
    private readonly iconReg: SvgIconRegistryService,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }

  ngOnInit(): void {
    if (this.inlays.length) {
      this.galleryId = `release-inlays-${this.inlays[0].id}`;
      this.gallery.loadItems(this.galleryId, this.inlays.map(inlay => ({
        id: inlay.id,
        title: inlay.title,
        thumbUrl: inlay.imageUrl ?? inlay.fullImageUrl ?? '',
        largeUrl: inlay.fullImageUrl ?? inlay.imageUrl ?? '',
        detailsUrl: inlay.downloadUrl,
      })));
    }
  }

  trackById(_: number, inlay: ProdReleaseInlayDto): number {
    return inlay.id;
  }
}
