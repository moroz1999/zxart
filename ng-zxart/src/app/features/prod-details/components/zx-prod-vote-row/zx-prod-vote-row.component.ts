import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ProdVotingDto} from '../../models/prod-core.dto';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';

@Component({
  selector: 'zx-prod-vote-row',
  standalone: true,
  imports: [CommonModule, ZxItemControlsComponent, TextDirective, ZxInlineComponent],
  templateUrl: './zx-prod-vote-row.component.html',
  styleUrls: ['./zx-prod-vote-row.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdVoteRowComponent {
  @Input({required: true}) elementId!: number;
  @Input() type = 'zxProd';
  @Input({required: true}) voting!: ProdVotingDto;
}
