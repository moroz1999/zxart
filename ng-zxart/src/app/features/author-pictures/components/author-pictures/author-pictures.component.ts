import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {
  ZxPictureGridSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {ZxCaptionDirective, ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {
  PictureGalleryHostComponent
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {AuthorPicturesService} from '../../services/author-pictures.service';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';

interface YearGroup {
  year: number;
  pictures: ZxPictureDto[];
  startIndex: number;
}

@Component({
  selector: 'zx-author-pictures',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxPictureGridSkeletonComponent,
    ZxCaptionDirective,
    ZxHeading2Directive,
    ZxStackComponent,
    ZxPicturesGridDirective,
    PictureGalleryHostComponent,
  ],
  templateUrl: './author-pictures.component.html',
  styleUrls: ['./author-pictures.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorPicturesComponent implements OnInit {
  @Input() elementId = 0;

  loading = true;
  error = false;
  yearGroups: YearGroup[] = [];
  allPictures: ZxPictureDto[] = [];
  readonly galleryId = 'zx-author-pictures';

  constructor(
    private authorPicturesService: AuthorPicturesService,
    private pictureGalleryService: PictureGalleryService,
    private cdr: ChangeDetectorRef,
  ) {
  }

  ngOnInit(): void {
    this.loadData();
  }

  private loadData(): void {
    if (!this.elementId) {
      this.loading = false;
      this.error = true;
      this.cdr.markForCheck();
      return;
    }
    this.loading = true;
    this.error = false;
    this.authorPicturesService.getPictures(this.elementId).subscribe({
      next: pictures => {
        this.loading = false;
        this.buildGroups(pictures);
        this.pictureGalleryService.ensureGalleryLoaded(this.galleryId, this.allPictures);
        this.cdr.markForCheck();
      },
      error: () => {
        this.loading = false;
        this.error = true;
        this.cdr.markForCheck();
      },
    });
  }

  private buildGroups(pictures: ZxPictureDto[]): void {
    const sorted = [...pictures].sort((a, b) => {
      const yearA = a.year ? parseInt(a.year, 10) : 0;
      const yearB = b.year ? parseInt(b.year, 10) : 0;
      if (yearB !== yearA) return yearB - yearA;
      return a.title.localeCompare(b.title);
    });

    this.allPictures = sorted;

    const yearMap = new Map<number, ZxPictureDto[]>();
    for (const pic of sorted) {
      const year = pic.year ? parseInt(pic.year, 10) : 0;
      if (!yearMap.has(year)) yearMap.set(year, []);
      yearMap.get(year)!.push(pic);
    }

    let index = 0;
    this.yearGroups = [...yearMap.entries()].map(([year, group]) => {
      const startIndex = index;
      index += group.length;
      return {year, pictures: group, startIndex};
    });
  }
}
