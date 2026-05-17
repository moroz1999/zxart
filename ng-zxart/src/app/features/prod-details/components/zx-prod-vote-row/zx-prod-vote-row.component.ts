import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ProdVotingDto} from '../../models/prod-core.dto';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';

@Component({
  selector: 'zx-prod-vote-row',
  standalone: true,
  imports: [CommonModule, ZxItemControlsComponent, ZxInlineComponent],
  templateUrl: './zx-prod-vote-row.component.html',
  styleUrls: ['./zx-prod-vote-row.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdVoteRowComponent implements OnChanges {
  @Input({required: true}) elementId!: number;
  @Input() type = 'zxProd';
  @Input({required: true}) voting!: ProdVotingDto;

  currentRating = 0;

  constructor(private cdr: ChangeDetectorRef) {}

  ngOnChanges(): void {
    this.currentRating = this.voting.votes;
  }

  onVoteChange(rating: number): void {
    this.currentRating = rating;
    this.cdr.markForCheck();
  }
}
