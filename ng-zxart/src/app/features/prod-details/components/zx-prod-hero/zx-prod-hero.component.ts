import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ProdCategoryRefDto, ProdCoreDto} from '../../models/prod-core.dto';
import {ZxProdVoteRowComponent} from '../zx-prod-vote-row/zx-prod-vote-row.component';
import {ZxProdExternalLinksComponent} from '../zx-prod-external-links/zx-prod-external-links.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {
  ZxBodySmMutedDirective,
  ZxHeading1Directive,
} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'zx-prod-hero',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdVoteRowComponent,
    ZxProdExternalLinksComponent,
    ZxButtonComponent,
    ZxHeading1Directive,
    ZxBodySmMutedDirective,
  ],
  templateUrl: './zx-prod-hero.component.html',
  styleUrls: ['./zx-prod-hero.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdHeroComponent {
  @Input({required: true}) core!: ProdCoreDto;

  get externalLinkLabelKey(): string {
    if (this.core.legalStatus === 'insales') {
      return 'prod-details.purchase';
    }
    if (this.core.legalStatus === 'donationware') {
      return 'prod-details.donate';
    }
    return 'prod-details.open_externallink';
  }

  get leafCategories(): ProdCategoryRefDto[] {
    return this.core.categoriesPaths
      .map(path => path.categories[path.categories.length - 1])
      .filter((cat): cat is ProdCategoryRefDto => !!cat);
  }
}
