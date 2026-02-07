import {ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxTuneDto} from '../../models/zx-tune-dto';
import {RatingComponent} from '../../components/rating/rating.component';
import {VoteService} from '../../services/vote.service';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';

@Component({
  selector: 'zx-tune-row',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    RatingComponent,
    ZxBadgeComponent,
  ],
  templateUrl: './zx-tune-row.component.html',
  styleUrls: ['./zx-tune-row.component.scss']
})
export class ZxTuneRowComponent {
  @Input() tune!: ZxTuneDto;
  @Input() index?: number;

  constructor(
    private voteService: VoteService,
    private cdr: ChangeDetectorRef,
  ) {}

  get medalClass(): string | null {
    if (!this.tune.party?.place) return null;
    switch (this.tune.party.place) {
      case 1: return 'medal-gold';
      case 2: return 'medal-silver';
      case 3: return 'medal-bronze';
      default: return null;
    }
  }

  vote(rating: number): void {
    this.voteService.send<'zxMusic'>(this.tune.id, rating, 'zxMusic').subscribe(value => {
      this.tune = {...this.tune, votes: value, userVote: rating};
      this.cdr.detectChanges();
    });
  }
}
