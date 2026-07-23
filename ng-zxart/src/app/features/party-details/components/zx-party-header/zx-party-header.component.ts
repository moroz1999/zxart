import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {PartyCoreDto} from '../../models/party-core.dto';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxHeroComponent} from '../../../../shared/ui/zx-hero/zx-hero.component';
import {ZxHeroTitleComponent} from '../../../../shared/ui/zx-hero-title/zx-hero-title.component';
import {ZxHeroBarComponent} from '../../../../shared/ui/zx-hero-bar/zx-hero-bar.component';
import {ZxFactsComponent} from '../../../../shared/ui/zx-facts/zx-facts.component';
import {ZxFactComponent} from '../../../../shared/ui/zx-facts/zx-fact.component';
import {ZxLocationComponent} from '../../../../shared/ui/zx-location/zx-location.component';
import {ZxMetaRowComponent} from '../../../../shared/ui/zx-meta-row/zx-meta-row.component';
import {ZxExtLinksComponent} from '../../../../shared/ui/zx-ext-links/zx-ext-links.component';
import {ZxCounterItem, ZxCountersComponent} from '../../../../shared/ui/zx-counters/zx-counters.component';
import {ZxDownloadButtonComponent} from '../../../../shared/ui/zx-download-button/zx-download-button.component';
import {ZxPartyEditingControlsComponent} from '../zx-party-editing-controls/zx-party-editing-controls.component';

@Component({
  selector: 'zx-party-header',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxButtonControlsComponent,
    ZxChipComponent,
    ZxPartyEditingControlsComponent,
    ZxHeroComponent,
    ZxHeroTitleComponent,
    ZxHeroBarComponent,
    ZxFactsComponent,
    ZxFactComponent,
    ZxLocationComponent,
    ZxMetaRowComponent,
    ZxExtLinksComponent,
    ZxCountersComponent,
    ZxDownloadButtonComponent,
  ],
  templateUrl: './zx-party-header.component.html',
  styleUrl: './zx-party-header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyHeaderComponent {
  @Input() core!: PartyCoreDto;

  get counters(): ZxCounterItem[] {
    return [
      {value: this.core.counters.compos, labelKey: 'party-details.header.counter.compos'},
      {value: this.core.counters.entries, labelKey: 'party-details.header.counter.entries'},
      {value: this.core.counters.authors, labelKey: 'party-details.header.counter.authors'},
    ];
  }
}
