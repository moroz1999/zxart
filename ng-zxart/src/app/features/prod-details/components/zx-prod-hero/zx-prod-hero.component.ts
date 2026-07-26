import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {ProdCategoryRefDto, ProdCoreDto} from '../../models/prod-core.dto';
import {ZxProdEditingControlsComponent} from '../zx-prod-editing-controls/zx-prod-editing-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxCalloutComponent} from '../../../../shared/ui/zx-callout/zx-callout.component';
import {ZxAddedByComponent} from '../../../../shared/ui/zx-added-by/zx-added-by.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxProdPeopleRowComponent} from '../../../../shared/ui/zx-prod-people-row/zx-prod-people-row.component';
import {ZxYoutubeEmbedComponent} from '../../../../shared/ui/zx-youtube-embed/zx-youtube-embed.component';
import {ZxHeroComponent} from '../../../../shared/ui/zx-hero/zx-hero.component';
import {ZxHeroTitleComponent} from '../../../../shared/ui/zx-hero-title/zx-hero-title.component';
import {ZxHeroBarComponent} from '../../../../shared/ui/zx-hero-bar/zx-hero-bar.component';
import {ZxFactsComponent} from '../../../../shared/ui/zx-facts/zx-facts.component';
import {ZxFactComponent} from '../../../../shared/ui/zx-facts/zx-fact.component';
import {ZxMetaRowComponent} from '../../../../shared/ui/zx-meta-row/zx-meta-row.component';
import {ZxExtLinkDto, ZxExtLinksComponent} from '../../../../shared/ui/zx-ext-links/zx-ext-links.component';
import {ZxCounterItem, ZxCountersComponent} from '../../../../shared/ui/zx-counters/zx-counters.component';
import {ZxPartyProvenanceComponent} from '../../../../shared/lib/zx-party-provenance/zx-party-provenance.component';

@Component({
  selector: 'zx-prod-hero',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxProdEditingControlsComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxChipComponent,
    ZxInlineComponent,
    ZxCalloutComponent,
    ZxAddedByComponent,
    ZxItemControlsComponent,
    ZxProdPeopleRowComponent,
    ZxYoutubeEmbedComponent,
    ZxHeroComponent,
    ZxHeroTitleComponent,
    ZxHeroBarComponent,
    ZxFactsComponent,
    ZxFactComponent,
    ZxMetaRowComponent,
    ZxExtLinksComponent,
    ZxCountersComponent,
    ZxPartyProvenanceComponent,
  ],
  templateUrl: './zx-prod-hero.component.html',
  styleUrls: ['./zx-prod-hero.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdHeroComponent implements OnInit {
  @Input({required: true}) core!: ProdCoreDto;

  constructor(
    private readonly translate: TranslateService,
    private readonly iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}cart.svg`, 'cart')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}dollar.svg`, 'dollar')?.subscribe();
  }

  get showDonateButton(): boolean {
    return this.core.legalStatus === 'donationware' && this.core.externalLink !== '';
  }

  get showBuyButton(): boolean {
    return this.core.legalStatus === 'insales' && this.core.externalLink !== '';
  }

  /** With a donate/buy button in the hero bar, the plain link is the homepage. */
  get externalLinkLabelKey(): string {
    if (this.showDonateButton || this.showBuyButton) {
      return 'prod-details.homepage';
    }
    return 'prod-details.open_externallink';
  }

  get leafCategories(): ProdCategoryRefDto[] {
    return this.core.categoriesPaths
      .map(path => path.categories[path.categories.length - 1])
      .filter((cat): cat is ProdCategoryRefDto => !!cat);
  }

  get showLegalStatus(): boolean {
    return this.core.legalStatus !== 'unknown';
  }

  /** The prod's own site plus its catalogued outbound links, in one meta row. */
  get externalLinks(): ZxExtLinkDto[] {
    const links: ZxExtLinkDto[] = [];
    if (this.core.externalLink) {
      links.push({url: this.core.externalLink, label: this.translate.instant(this.externalLinkLabelKey)});
    }
    for (const link of this.core.links) {
      links.push({url: link.url, label: link.name});
    }
    return links;
  }

  get counters(): ZxCounterItem[] {
    const items: ZxCounterItem[] = [];
    if (this.core.voting.votes > 0) {
      items.push({value: this.core.voting.votes.toFixed(2), labelKey: 'hero.rating'});
    }
    items.push({value: this.core.voting.votesAmount, labelKey: 'hero.votes'});
    items.push({value: this.core.downloadsCount, labelKey: 'hero.downloads'});
    items.push({value: this.core.playsCount, labelKey: 'hero.plays'});
    return items;
  }
}
