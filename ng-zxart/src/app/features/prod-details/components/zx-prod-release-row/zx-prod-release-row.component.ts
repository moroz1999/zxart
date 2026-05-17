import {ChangeDetectionStrategy, Component, Input, OnInit,} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {EmulatorModalService} from '../../../emulator/services/emulator-modal.service';
import {EmulatorType} from '../../../emulator/engines/emulator-engine';
import {ProdReleaseDto} from '../../models/prod-release.dto';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxProdLanguageLinksComponent,} from '../zx-prod-language-links/zx-prod-language-links.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {environment} from '../../../../../environments/environment';
import {ZxReleaseTypeBadgeComponent} from '../../../../shared/ui/zx-release-type-badge/zx-release-type-badge.component';
import {LightboxModule} from 'ng-gallery/lightbox';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {ProdFileDto} from '../../models/prod-file.dto';

const SUPPORTED_EMULATOR_TYPES: ReadonlyArray<EmulatorType> = ['usp', 'zx81', 'tsconf', 'samcoupe', 'zxnext'];

@Component({
  selector: 'tr[zxProdReleaseRow]',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxProdLanguageLinksComponent,
    ZxInlineComponent,
    ZxStackComponent,
    ZxReleaseTypeBadgeComponent,
    LightboxModule,
  ],
  templateUrl: './zx-prod-release-row.component.html',
  styleUrls: ['./zx-prod-release-row.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleaseRowComponent implements OnInit {
  @Input({required: true}) release!: ProdReleaseDto;
  @Input({required: true}) canUploadScreenshot!: boolean;
  @Input({required: true}) screenshotUploadUrl!: string;

  galleryId = '';

  constructor(
    private readonly emulator: EmulatorModalService,
    private readonly iconReg: SvgIconRegistryService,
    private readonly gallery: PictureGalleryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}play.svg`, 'play')?.subscribe();
    if (this.release.screenshots.length > 0) {
      this.galleryId = `zx-release-screenshots-${this.release.id}`;
      this.gallery.loadItems(this.galleryId, this.release.screenshots.map(this.toGalleryItem));
    }
  }

  private toGalleryItem(file: ProdFileDto) {
    return {
      id: file.id,
      title: file.title,
      thumbUrl: file.imageUrl ?? file.fullImageUrl ?? '',
      largeUrl: file.fullImageUrl ?? file.imageUrl ?? '',
      detailsUrl: file.downloadUrl,
    };
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
      uploadUrl: this.screenshotUploadUrl,
      canScreenshot: this.canUploadScreenshot,
    });
  }

}
