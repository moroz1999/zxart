import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {ReleaseDetailsDto} from '../../models/release-details.dto';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxDownloadButtonComponent} from '../../../../shared/ui/zx-download-button/zx-download-button.component';
import {ZxReleaseTypeBadgeComponent} from '../../../../shared/ui/zx-release-type-badge/zx-release-type-badge.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxProdPeopleRowComponent} from '../../../../shared/ui/zx-prod-people-row/zx-prod-people-row.component';
import {ZxReleaseEditingControlsComponent} from '../zx-release-editing-controls/zx-release-editing-controls.component';
import {ZxReleaseParentAnchorComponent} from '../zx-release-parent-anchor/zx-release-parent-anchor.component';
import {ZxAddedByComponent} from '../../../../shared/ui/zx-added-by/zx-added-by.component';
import {ZxHardwareIconComponent} from '../../../../shared/ui/zx-hardware-icon/zx-hardware-icon.component';
import {ZxCalloutComponent} from '../../../../shared/ui/zx-callout/zx-callout.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxEmulatorPlayButtonComponent} from '../../../../shared/ui/zx-emulator-play-button/zx-emulator-play-button.component';
import {ZxCardScreenshotGalleryComponent} from '../../../../shared/ui/zx-card-screenshot-preview/zx-card-screenshot-gallery.component';
import {ZxHeroComponent} from '../../../../shared/ui/zx-hero/zx-hero.component';
import {ZxHeroTitleComponent} from '../../../../shared/ui/zx-hero-title/zx-hero-title.component';
import {ZxHeroBarComponent} from '../../../../shared/ui/zx-hero-bar/zx-hero-bar.component';
import {ZxFactsComponent} from '../../../../shared/ui/zx-facts/zx-facts.component';
import {ZxFactComponent} from '../../../../shared/ui/zx-facts/zx-fact.component';
import {ZxCounterItem, ZxCountersComponent} from '../../../../shared/ui/zx-counters/zx-counters.component';
import {ZxPartyProvenanceComponent} from '../../../../shared/lib/zx-party-provenance/zx-party-provenance.component';

@Component({
  selector: 'zx-release-hero',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxDownloadButtonComponent,
    ZxReleaseTypeBadgeComponent,
    ZxInlineComponent,
    ZxChipComponent,
    ZxProdPeopleRowComponent,
    ZxReleaseEditingControlsComponent,
    ZxReleaseParentAnchorComponent,
    ZxAddedByComponent,
    ZxHardwareIconComponent,
    ZxCalloutComponent,
    ZxItemControlsComponent,
    ZxEmulatorPlayButtonComponent,
    ZxCardScreenshotGalleryComponent,
    ZxHeroComponent,
    ZxHeroTitleComponent,
    ZxHeroBarComponent,
    ZxFactsComponent,
    ZxFactComponent,
    ZxCountersComponent,
    ZxPartyProvenanceComponent,
  ],
  templateUrl: './zx-release-hero.component.html',
  styleUrl: './zx-release-hero.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseHeroComponent implements OnInit {
  @Input({required: true}) details!: ReleaseDetailsDto;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}cart.svg`, 'cart')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}dollar.svg`, 'dollar')?.subscribe();
  }

  get showDonateButton(): boolean {
    return this.details.prodLegalStatus === 'donationware'
      && this.details.prodExternalLink !== '';
  }

  get showBuyButton(): boolean {
    return this.details.prodLegalStatus === 'insales'
      && this.details.prodExternalLink !== '';
  }

  get screenshotUrls(): string[] {
    return this.details.screenshots
      .map(screenshot => screenshot.imageUrl)
      .filter((url): url is string => !!url);
  }

  get counters(): ZxCounterItem[] {
    const items: ZxCounterItem[] = [];
    if (this.details.votes.votes > 0) {
      items.push({value: this.details.votes.votes.toFixed(2), labelKey: 'hero.rating'});
    }
    items.push({value: this.details.downloadsCount, labelKey: 'hero.downloads'});
    items.push({value: this.details.playsCount, labelKey: 'hero.plays'});
    return items;
  }
}
