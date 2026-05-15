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

const SUPPORTED_EMULATOR_TYPES: ReadonlyArray<EmulatorType> = ['usp', 'zx81', 'tsconf', 'samcoupe', 'zxnext'];

@Component({
  selector: 'tbody[zxProdReleaseRow]',
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
  ],
  templateUrl: './zx-prod-release-row.component.html',
  styleUrls: ['./zx-prod-release-row.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleaseRowComponent implements OnInit {
  @Input({required: true}) release!: ProdReleaseDto;
  @Input({required: true}) canUploadScreenshot!: boolean;
  @Input({required: true}) screenshotUploadUrl!: string;

  constructor(
    private readonly emulator: EmulatorModalService,
    private readonly iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
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
