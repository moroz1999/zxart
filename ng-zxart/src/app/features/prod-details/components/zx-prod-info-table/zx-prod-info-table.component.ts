import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxProdLanguageLinksComponent} from '../zx-prod-language-links/zx-prod-language-links.component';
import {ZxProdExternalLinksComponent} from '../zx-prod-external-links/zx-prod-external-links.component';
import {ZxProdVoteRowComponent} from '../zx-prod-vote-row/zx-prod-vote-row.component';
import {ProdCoreDto} from '../../models/prod-core.dto';

@Component({
  selector: 'zx-prod-info-table',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdLanguageLinksComponent,
    ZxProdExternalLinksComponent,
    ZxProdVoteRowComponent,
  ],
  templateUrl: './zx-prod-info-table.component.html',
  styleUrls: ['./zx-prod-info-table.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInfoTableComponent {
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

  get externalLinkButtonClass(): string {
    return this.core.legalStatus === 'insales' || this.core.legalStatus === 'donationware'
      ? 'release-sales-button'
      : '';
  }
}
