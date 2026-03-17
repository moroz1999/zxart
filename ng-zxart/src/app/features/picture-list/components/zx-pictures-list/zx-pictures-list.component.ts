import {Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {
  PictureGalleryHostComponent
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureListService} from '../../services/picture-list.service';

@Component({
  selector: 'zx-pictures-list',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxSkeletonComponent,
    ZxCaptionDirective,
    ZxPicturesGridDirective,
    PictureGalleryHostComponent,
  ],
  templateUrl: './zx-pictures-list.component.html',
  styleUrls: ['./zx-pictures-list.component.scss'],
})
export class ZxPicturesListComponent implements OnInit {
  @Input() elementId = 0;
  @Input() compoType = '';

  loading = true;
  error = false;
  pictures: ZxPictureDto[] = [];
  galleryId = '';

  constructor(private pictureListService: PictureListService) {}

  ngOnInit(): void {
    this.galleryId = `zx-pictures-list-${this.elementId}${this.compoType ? '-' + this.compoType : ''}`;
    this.loadData();
  }

  private loadData(): void {
    if (!this.elementId) {
      this.loading = false;
      this.error = true;
      return;
    }
    this.loading = true;
    this.error = false;
    this.pictureListService.getPictures(this.elementId, this.compoType || undefined).subscribe({
      next: pictures => {
        this.loading = false;
        this.pictures = pictures;
      },
      error: () => {
        this.loading = false;
        this.error = true;
      },
    });
  }
}
