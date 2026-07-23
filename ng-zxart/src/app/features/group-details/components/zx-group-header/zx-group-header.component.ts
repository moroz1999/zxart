import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {RouterLink} from '@angular/router';
import {GroupCoreDto} from '../../models/group-core.dto';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxGroupEditingControlsComponent} from '../zx-group-editing-controls/zx-group-editing-controls.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxHeroComponent} from '../../../../shared/ui/zx-hero/zx-hero.component';
import {ZxHeroTitleComponent} from '../../../../shared/ui/zx-hero-title/zx-hero-title.component';
import {ZxFactsComponent} from '../../../../shared/ui/zx-facts/zx-facts.component';
import {ZxFactComponent} from '../../../../shared/ui/zx-facts/zx-fact.component';
import {ZxLocationComponent} from '../../../../shared/ui/zx-location/zx-location.component';
import {ZxMetaRowComponent} from '../../../../shared/ui/zx-meta-row/zx-meta-row.component';
import {ZxExtLinksComponent} from '../../../../shared/ui/zx-ext-links/zx-ext-links.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-group-header',
  standalone: true,
  imports: [
    RouterLink,
    CommonModule,
    TranslateModule,
    ZxChipComponent,
    ZxGroupEditingControlsComponent,
    SvgIconComponent,
    TextDirective,
    ZxHeroComponent,
    ZxHeroTitleComponent,
    ZxFactsComponent,
    ZxFactComponent,
    ZxLocationComponent,
    ZxMetaRowComponent,
    ZxExtLinksComponent,
  ],
  templateUrl: './zx-group-header.component.html',
  styleUrl: './zx-group-header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupHeaderComponent implements OnInit {
  @Input() core!: GroupCoreDto;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
  }

  natureColor(nature: string): ZxChipColor {
    if (nature === 'developer') return 'primary';
    if (nature === 'publisher') return 'artist';
    return 'code';
  }
}
