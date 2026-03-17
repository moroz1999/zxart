import {Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {
  PictureGalleryHostComponent
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureListService} from '../../services/picture-list.service';

@Component({
  selector: 'zx-pictures-related',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxPicturesGridDirective,
    PictureGalleryHostComponent,
  ],
  templateUrl: './zx-pictures-related.component.html',
  styleUrls: ['./zx-pictures-related.component.scss'],
})
export class ZxPicturesRelatedComponent implements OnInit {
  @Input() pictureId = 0;

  type: 'game' | 'authors' | 'none' = 'none';
  pictures: ZxPictureDto[] = [];
  galleryId = '';

  constructor(private pictureListService: PictureListService) {}

  ngOnInit(): void {
    this.galleryId = `zx-pictures-related-${this.pictureId}`;
    if (!this.pictureId) return;

    this.pictureListService.getRelated(this.pictureId).subscribe(response => {
      this.type = response.type;
      this.pictures = response.items;
    });
  }
}
