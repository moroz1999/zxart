import {Component, Input, OnInit} from '@angular/core';
import {RatingComponent} from '../../components/rating/rating.component';
import {VoteService} from '../../services/vote.service';

/**
 * Legacy bridge component — for use in Smarty templates only.
 *
 * This component is a thin adapter between the legacy Smarty template system and
 * the Angular `zx-rating` component. It receives attributes as plain HTML strings
 * (element-id, type, votes, user-vote, deny-voting) and delegates rendering to
 * `zx-rating`.
 *
 * @deprecated Do not use in new Angular code. Use `zx-rating` directly instead.
 */
@Component({
  selector: 'zx-vote',
  standalone: true,
  imports: [RatingComponent],
  templateUrl: './zx-vote.component.html',
  styleUrls: ['./zx-vote.component.scss'],
})
export class ZxVoteComponent implements OnInit {
  @Input({alias: 'element-id'}) elementId!: number;
  @Input() type!: string;
  @Input() votes: number = 0;
  @Input({alias: 'votes-amount'}) votesAmount: number = 0;
  @Input({alias: 'user-vote'}) userVote: number = 0;
  @Input({alias: 'deny-voting'}) denyVoting: boolean = false;

  overallRating: number = 0;
  currentVotesAmount: number = 0;
  currentUserVote: number | undefined;

  constructor(private voteService: VoteService) {}

  ngOnInit(): void {
    this.overallRating = Number(this.votes) || 0;
    this.currentVotesAmount = Number(this.votesAmount) || 0;
    this.currentUserVote = Number(this.userVote) || undefined;
    this.denyVoting = this.denyVoting !== false && (this.denyVoting as unknown) !== 'false';
  }

  vote(rating: number): void {
    this.voteService.send(this.elementId, rating, this.type).subscribe(result => {
      this.overallRating = result.votes;
      this.currentVotesAmount = result.votesAmount;
      this.currentUserVote = rating || undefined;
    });
  }
}
