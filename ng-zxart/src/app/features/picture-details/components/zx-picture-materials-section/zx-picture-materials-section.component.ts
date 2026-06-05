import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Lightbox} from 'ng-gallery/lightbox';
import {PictureMaterialDto} from '../../models/picture-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';

@Component({
  selector: 'zx-picture-materials-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxGridComponent,
    ZxStackComponent,
    TextDirective,
    HeadingDirective,
    PictureGalleryHostComponent,
  ],
  templateUrl: './zx-picture-materials-section.component.html',
  styleUrls: ['./zx-picture-materials-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureMaterialsSectionComponent implements OnInit {
  @Input({required: true}) materials: PictureMaterialDto[] = [];

  readonly galleryId = 'zx-picture-materials';

  constructor(
    private readonly gallery: PictureGalleryService,
    private readonly lightbox: Lightbox,
  ) {}

  ngOnInit(): void {
    if (this.materials.length) {
      this.gallery.loadItems(this.galleryId, this.materials.map((m, i) => this.toGalleryItem(m, i)));
    }
  }

  openAt(index: number): void {
    this.lightbox.open(index, this.galleryId);
  }

  labelKey(material: PictureMaterialDto): string {
    return `picture-details.material-${material.kind}`;
  }

  private toGalleryItem(material: PictureMaterialDto, index: number): PictureGalleryItem {
    return {
      id: index,
      title: material.label,
      thumbUrl: material.imageUrl,
      largeUrl: material.imageUrl,
      detailsUrl: material.imageUrl,
    };
  }
}
