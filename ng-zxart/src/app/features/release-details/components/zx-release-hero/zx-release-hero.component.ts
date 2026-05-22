import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ReleaseDetailsDto} from '../../models/release-details.dto';
import {ZxReleaseTypeBadgeComponent} from '../../../../shared/ui/zx-release-type-badge/zx-release-type-badge.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxProdPeopleRowComponent} from '../../../../shared/ui/zx-prod-people-row/zx-prod-people-row.component';

@Component({
  selector: 'zx-release-hero',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxReleaseTypeBadgeComponent,
    ZxInlineComponent,
    HeadingDirective,
    TextDirective,
    ZxProdPeopleRowComponent,
  ],
  templateUrl: './zx-release-hero.component.html',
  styleUrl: './zx-release-hero.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseHeroComponent {
  @Input({required: true}) details!: ReleaseDetailsDto;
}
