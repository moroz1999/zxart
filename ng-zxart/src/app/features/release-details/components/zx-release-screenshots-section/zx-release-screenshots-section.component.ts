import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit} from '@angular/core';
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
import {ScreenshotMoveApiService} from '../../../prod-details/services/screenshot-move-api.service';

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
  @Input({required: true}) elementId!: number;
  @Input({required: true}) screenshots!: ProdFileDto[];
  @Input() canReorder = false;

  galleryId = '';
  files: ProdFileDto[] = [];
  moving = false;

  constructor(
    private readonly gallery: PictureGalleryService,
    private readonly moveApi: ScreenshotMoveApiService,
    private readonly cdr: ChangeDetectorRef,
    private readonly iconReg: SvgIconRegistryService,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}skip-previous.svg`, 'skip-previous')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}skip-next.svg`, 'skip-next')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }

  ngOnInit(): void {
    this.files = this.screenshots;
    if (this.files.length) {
      this.galleryId = `release-screenshots-${this.files[0].id}`;
      this.loadGalleryItems();
    }
  }

  trackById(_: number, file: ProdFileDto): number {
    return file.id;
  }

  onMove(file: ProdFileDto, direction: 'left' | 'right'): void {
    if (this.moving) {
      return;
    }
    this.moving = true;
    this.moveApi.move(this.elementId, file.id, direction).subscribe(files => {
      this.moving = false;
      if (files !== null) {
        this.files = files;
        this.loadGalleryItems();
      }
      this.cdr.markForCheck();
    });
  }

  isFirst(file: ProdFileDto): boolean {
    return this.files[0]?.id === file.id;
  }

  isLast(file: ProdFileDto): boolean {
    return this.files[this.files.length - 1]?.id === file.id;
  }

  private loadGalleryItems(): void {
    this.gallery.loadItems(this.galleryId, this.files.map(file => ({
      id: file.id,
      title: file.title,
      thumbUrl: file.imageUrl ?? file.fullImageUrl ?? '',
      largeUrl: file.fullImageUrl ?? file.imageUrl ?? '',
      detailsUrl: file.downloadUrl,
    })));
  }
}
