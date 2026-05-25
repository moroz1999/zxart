import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {AuthorCoreDto} from '../../models/author-core.dto';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxAuthorEditingControlsComponent} from '../zx-author-editing-controls/zx-author-editing-controls.component';

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
  ],
  templateUrl: './zx-author-header.component.html',
  styleUrl: './zx-author-header.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorHeaderComponent {
  @Input() core!: AuthorCoreDto;

  showAllAliases = false;
  showTech = false;

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
