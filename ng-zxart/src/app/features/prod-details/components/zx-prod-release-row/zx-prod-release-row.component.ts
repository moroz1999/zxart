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
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {animate, style, transition, trigger} from '@angular/animations';

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
    CdkConnectedOverlay,
    CdkOverlayOrigin,
  ],
  templateUrl: './zx-prod-release-row.component.html',
  styleUrls: ['./zx-prod-release-row.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [
    trigger('screenshotPreview', [
      transition(':enter', [
        style({opacity: 0, transform: 'scale(0.94)'}),
        animate('180ms cubic-bezier(0.22, 1, 0.36, 1)', style({opacity: 1, transform: 'scale(1)'})),
      ]),
      transition(':leave', [
        animate('120ms cubic-bezier(0.64, 0, 0.78, 0)', style({opacity: 0, transform: 'scale(0.97)'})),
      ]),
    ]),
  ],
})
export class ZxProdReleaseRowComponent implements OnInit {
  @Input({required: true}) release!: ProdReleaseDto;
  @Input({required: true}) canUploadScreenshot!: boolean;
  @Input({required: true}) screenshotUploadUrl!: string;

  galleryId = '';
  previewOpen = false;

  readonly previewPositions: ConnectedPosition[] = [
    {originX: 'end', originY: 'top', overlayX: 'start', overlayY: 'top', offsetX: 8},
    {originX: 'start', originY: 'top', overlayX: 'end', overlayY: 'top', offsetX: -8},
  ];

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

  showPreview(): void {
    this.previewOpen = true;
  }

  hidePreview(): void {
    this.previewOpen = false;
  }

  get previewImageUrl(): string {
    const screenshot = this.release.screenshots[0];
    return screenshot?.fullImageUrl ?? screenshot?.imageUrl ?? '';
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
