import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {LightboxModule} from 'ng-gallery/lightbox';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ProdCoverGroupDto, ProdReleaseInlayDto} from '../../../prod-details/models/prod-release-inlay.dto';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {ZxReleaseSectionHeadComponent} from '../zx-release-section-head/zx-release-section-head.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
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
    ZxStackComponent,
    ZxInlineComponent,
  ],
  templateUrl: './zx-release-inlays-section.component.html',
  styleUrl: './zx-release-inlays-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseInlaysSectionComponent implements OnInit {
  @Input({required: true}) groups!: ProdCoverGroupDto[];

  galleryId = '';

  // One lightbox spans every group, so a cover's position is its index in the flattened list.
  private galleryPositions = new Map<number, number>();

  constructor(
    private readonly gallery: PictureGalleryService,
    private readonly iconReg: SvgIconRegistryService,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }

  ngOnInit(): void {
    const covers = this.groups.flatMap(group => group.items);
    if (!covers.length) {
      return;
    }
    this.galleryId = `release-covers-${covers[0].id}`;
    this.galleryPositions = new Map(covers.map((cover, index) => [cover.id, index]));
    this.gallery.loadItems(this.galleryId, covers.map(cover => ({
      id: cover.id,
      title: cover.title,
      thumbUrl: cover.imageUrl ?? cover.fullImageUrl ?? '',
      largeUrl: cover.fullImageUrl ?? cover.imageUrl ?? '',
      detailsUrl: cover.downloadUrl,
    })));
  }

  galleryPosition(cover: ProdReleaseInlayDto): number {
    return this.galleryPositions.get(cover.id) ?? 0;
  }

  trackByKind(_: number, group: ProdCoverGroupDto): string {
    return group.kind;
  }

  trackById(_: number, cover: ProdReleaseInlayDto): number {
    return cover.id;
  }
}
