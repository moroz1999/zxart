import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit,} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {LightboxModule} from 'ng-gallery/lightbox';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {
  ZxScreenshotGridSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-screenshot-grid-skeleton/zx-screenshot-grid-skeleton.component';
import {
  PictureGalleryHostComponent,
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';
import {EmulatorModalService} from '../../../emulator/services/emulator-modal.service';
import {EmulatorType} from '../../../emulator/engines/emulator-engine';
import {ProdReleaseDto} from '../../models/prod-release.dto';
import {ProdFileDto} from '../../models/prod-file.dto';
import {ReleaseScreenshotsApiService} from '../../services/release-screenshots-api.service';
import {ZxProdLanguageLinksComponent,} from '../zx-prod-language-links/zx-prod-language-links.component';
import {ZxProdExternalLinksComponent,} from '../zx-prod-external-links/zx-prod-external-links.component';

const SUPPORTED_EMULATOR_TYPES: ReadonlyArray<EmulatorType> = ['usp', 'zx81', 'tsconf', 'samcoupe', 'zxnext'];

@Component({
  selector: 'tbody[zxProdReleaseRow]',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    LightboxModule,
    InViewportDirective,
    ZxButtonComponent,
    ZxScreenshotGridSkeletonComponent,
    PictureGalleryHostComponent,
    ZxProdLanguageLinksComponent,
    ZxProdExternalLinksComponent,
  ],
  templateUrl: './zx-prod-release-row.component.html',
  styleUrls: ['./zx-prod-release-row.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleaseRowComponent implements OnInit {
  @Input({required: true}) release!: ProdReleaseDto;
  @Input({required: true}) prodUrl!: string;

  screenshotsLoading = false;
  screenshotsLoaded = false;
  screenshots: ProdFileDto[] = [];
  galleryId = '';

  constructor(
    private readonly api: ReleaseScreenshotsApiService,
    private readonly gallery: PictureGalleryService,
    private readonly emulator: EmulatorModalService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.galleryId = `zx-release-screenshots-${this.release.id}`;
  }

  get medalClass(): string | null {
    if (!this.release.party?.place) {
      return null;
    }
    switch (this.release.party.place) {
      case 1: return 'medal-gold';
      case 2: return 'medal-silver';
      case 3: return 'medal-bronze';
      default: return null;
    }
  }

  get supportedEmulatorType(): EmulatorType | null {
    const type = this.release.emulatorType;
    if (!type) {
      return null;
    }
    return SUPPORTED_EMULATOR_TYPES.includes(type as EmulatorType) ? (type as EmulatorType) : null;
  }

  get canPlay(): boolean {
    return this.release.isPlayable
      && this.release.isDownloadable
      && this.release.playUrl !== null
      && this.supportedEmulatorType !== null;
  }

  get showSalesButton(): boolean {
    return this.release.prodLegalStatus === 'donationware' && this.release.prodExternalLink !== '';
  }

  get showPurchaseButton(): boolean {
    return !this.release.isDownloadable
      && this.release.prodExternalLink !== ''
      && this.release.prodLegalStatus === 'insales';
  }

  get showOpenLinkButton(): boolean {
    return !this.release.isDownloadable
      && this.release.prodExternalLink !== ''
      && this.release.prodLegalStatus !== 'insales';
  }

  onPlay(): void {
    const type = this.supportedEmulatorType;
    if (!type || !this.release.playUrl) {
      return;
    }
    this.emulator.open({
      emulatorType: type,
      fileUrl: this.release.playUrl,
      uploadUrl: `${this.prodUrl}id:${this.release.id}/action:uploadScreenshot/`,
      canScreenshot: false,
    });
  }

  onScreenshotsInViewport(): void {
    if (this.screenshotsLoaded || this.screenshotsLoading) {
      return;
    }
    this.screenshotsLoading = true;
    this.api.getScreenshots(this.release.id).subscribe(files => {
      this.screenshots = files;
      this.screenshotsLoaded = true;
      this.screenshotsLoading = false;
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
