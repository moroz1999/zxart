import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {FirstpageProdDto} from '../../models/firstpage-prod-dto';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';
import {RatingComponent} from '../../components/rating/rating.component';
import {VoteService} from '../../services/vote.service';

@Component({
  selector: 'zx-prod-card',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    RatingComponent,
  ],
  templateUrl: './zx-prod-card.component.html',
  styleUrls: ['./zx-prod-card.component.scss']
})
export class ZxProdCardComponent {
  @Input() prod!: FirstpageProdDto;

  constructor(private voteService: VoteService) {}

  vote(rating: number): void {
    this.voteService.send<'zxProd'>(this.prod.id, rating, 'zxProd').subscribe();
  }
}
