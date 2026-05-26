import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {AuthorCoreDto} from '../../models/author-core.dto';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxAuthorEditingControlsComponent} from '../zx-author-editing-controls/zx-author-editing-controls.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
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
    TextDirective,
    HeadingDirective,
  ],
  templateUrl: './zx-author-header.component.html',
  styleUrl: './zx-author-header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorHeaderComponent implements OnInit {
  @Input() core!: AuthorCoreDto;

  showAllAliases = false;
  showTech = false;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}person.svg`, 'person')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}location.svg`, 'location')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}image.svg`, 'image')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}music-note.svg`, 'music-note')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}expand-more.svg`, 'expand-more')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}expand-less.svg`, 'expand-less')?.subscribe();
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

  toggleAliases(): void {
    this.showAllAliases = !this.showAllAliases;
  }

  toggleTech(): void {
    this.showTech = !this.showTech;
  }

  techValueKey(field: 'palette' | 'chiptype' | 'channelstype', value: string): string {
    return `author-details.header.tech-value.${field}.${value.toLowerCase()}`;
  }

  getRoleChipColor(role: string): ZxChipColor {
    if (role === 'musician') return 'primary';
    if (role === 'coder') return 'code';
    return 'artist';
  }
}
