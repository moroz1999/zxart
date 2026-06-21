import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {AuthorCoreDto} from '../../models/author-core.dto';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxAuthorEditingControlsComponent} from '../zx-author-editing-controls/zx-author-editing-controls.component';
import {
  RatingStripItem,
  ZxRatingStripComponent,
} from '../../../../shared/components/zx-rating-strip/zx-rating-strip.component';
import {TechSettingRow, ZxTechSettingsComponent} from '../../../../shared/ui/zx-tech-settings/zx-tech-settings.component';
import {environment} from '../../../../../environments/environment';

const VISIBLE_ALIASES = 7;

@Component({
  selector: 'zx-author-header',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxBadgeComponent,
    ZxChipComponent,
    ZxInlineComponent,
    ZxButtonComponent,
    ZxAuthorEditingControlsComponent,
    SvgIconComponent,
    ZxRatingStripComponent,
    ZxTechSettingsComponent,
  ],
  templateUrl: './zx-author-header.component.html',
  styleUrl: './zx-author-header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorHeaderComponent implements OnInit {
  @Input() core!: AuthorCoreDto;

  showAllAliases = false;

  constructor(
    private readonly iconReg: SvgIconRegistryService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}location.svg`, 'location')?.subscribe();
  }

  get visibleAliases() {
    if (this.showAllAliases) {
      return this.core.aliases;
    }
    return this.core.aliases.slice(0, VISIBLE_ALIASES);
  }

  get hiddenAliasCount(): number {
    return Math.max(0, this.core.aliases.length - VISIBLE_ALIASES);
  }

  get joinedYear(): string {
    return this.core.joined ? this.core.joined.slice(0, 4) : '';
  }

  get ratingItems(): RatingStripItem[] {
    return [
      {type: 'artist', value: this.core.ratings.artist},
      {type: 'musician', value: this.core.ratings.musician},
    ];
  }

  toggleAliases(): void {
    this.showAllAliases = !this.showAllAliases;
  }

  get techRows(): TechSettingRow[] {
    const tech = this.core.tech;
    const rows: TechSettingRow[] = [];
    if (tech.palette) {
      rows.push(this.techRow('palette', 'palette', tech.palette));
    }
    if (tech.ayChip) {
      rows.push(this.techRow('ay-chip', 'chiptype', tech.ayChip));
    }
    if (tech.ayChannels) {
      rows.push(this.techRow('ay-channels', 'channelstype', tech.ayChannels));
    }
    if (tech.ayClock) {
      rows.push({label: this.translate.instant('author-details.header.tech.ay-clock'), value: tech.ayClock});
    }
    if (tech.intFreq) {
      rows.push({label: this.translate.instant('author-details.header.tech.int-freq'), value: tech.intFreq});
    }
    return rows;
  }

  private techRow(labelKey: string, valueField: 'palette' | 'chiptype' | 'channelstype', value: string): TechSettingRow {
    return {
      label: this.translate.instant(`author-details.header.tech.${labelKey}`),
      value: this.translate.instant(`author-details.header.tech-value.${valueField}.${value.toLowerCase()}`),
    };
  }

  getRoleChipColor(role: string): ZxChipColor {
    if (role === 'musician') return 'primary';
    if (role === 'coder') return 'code';
    return 'artist';
  }
}
