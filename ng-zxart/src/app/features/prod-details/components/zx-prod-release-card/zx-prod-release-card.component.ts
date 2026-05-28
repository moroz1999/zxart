import {ChangeDetectionStrategy, Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {ZxCardScreenshotGalleryComponent} from '../../../../shared/ui/zx-card-screenshot-preview/zx-card-screenshot-gallery.component';
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
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';

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
    ZxChipComponent,
    ZxCardScreenshotGalleryComponent,
    ZxItemControlsComponent,
  ],
  templateUrl: './zx-prod-release-card.component.html',
  styleUrls: ['./zx-prod-release-card.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleaseCardComponent implements OnChanges, OnInit {
  @Input({required: true}) release!: ProdReleaseDto;
  @Input({required: true}) canUploadScreenshot!: boolean;
  @Input({required: true}) screenshotUploadUrl!: string;

  screenshotUrls: string[] = [];

  constructor(
    private readonly emulator: EmulatorModalService,
    private readonly iconReg: SvgIconRegistryService,
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['release']) {
      this.screenshotUrls = this.release.screenshots
        .map(screenshot => screenshot.imageUrl ?? screenshot.fullImageUrl ?? '')
        .filter((imageUrl): imageUrl is string => imageUrl !== '');
    }
  }

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}play.svg`, 'play')?.subscribe();
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
