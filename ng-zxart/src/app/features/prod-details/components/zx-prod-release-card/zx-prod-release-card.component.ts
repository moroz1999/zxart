import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {EmulatorModalService} from '../../../emulator/services/emulator-modal.service';
import {EmulatorType} from '../../../emulator/engines/emulator-engine';
import {ProdReleaseDto} from '../../models/prod-release.dto';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxProdLanguageLinksComponent} from '../zx-prod-language-links/zx-prod-language-links.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxInsetComponent} from '../../../../shared/ui/zx-inset/zx-inset.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {environment} from '../../../../../environments/environment';
import {ZxReleaseTypeBadgeComponent} from '../../../../shared/ui/zx-release-type-badge/zx-release-type-badge.component';
import {LightboxModule} from 'ng-gallery/lightbox';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {ProdFileDto} from '../../models/prod-file.dto';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';

const SUPPORTED_EMULATOR_TYPES: ReadonlyArray<EmulatorType> = ['usp', 'zx81', 'tsconf', 'samcoupe', 'zxnext'];

@Component({
  selector: 'zx-prod-release-card',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxProdLanguageLinksComponent,
    ZxInlineComponent,
    ZxInsetComponent,
    ZxPanelComponent,
    ZxReleaseTypeBadgeComponent,
    LightboxModule,
    ZxChipComponent,
  ],
  templateUrl: './zx-prod-release-card.component.html',
  styleUrls: ['./zx-prod-release-card.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleaseCardComponent implements OnInit {
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

  get canPlay(): boolean {
    const type = this.release.emulatorType;
    return this.release.isPlayable
      && this.release.isDownloadable
      && this.release.playUrl !== null
      && type !== null
      && SUPPORTED_EMULATOR_TYPES.includes(type as EmulatorType);
  }

  onPlay(): void {
    const type = this.release.emulatorType as EmulatorType | null;
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
