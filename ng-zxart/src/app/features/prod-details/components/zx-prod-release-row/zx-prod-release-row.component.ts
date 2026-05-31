import {ChangeDetectionStrategy, Component, Input, OnInit,} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
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
import {ZxEmulatorPlayButtonComponent} from '../../../../shared/ui/zx-emulator-play-button/zx-emulator-play-button.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxHardwareIconComponent} from '../../../../shared/ui/zx-hardware-icon/zx-hardware-icon.component';

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
    ZxEmulatorPlayButtonComponent,
    TextDirective,
    ZxHardwareIconComponent,
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

}
