import {Component, Input, OnInit} from '@angular/core';
import {RatingComponent} from '../../components/rating/rating.component';
import {VoteService} from '../../services/vote.service';

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
  @Input({alias: 'user-vote'}) userVote: number = 0;
  @Input({alias: 'deny-voting'}) denyVoting: boolean = false;

  overallRating: number = 0;
  currentUserVote: number | undefined;

  constructor(private voteService: VoteService) {}

  ngOnInit(): void {
    this.overallRating = Number(this.votes) || 0;
    this.currentUserVote = Number(this.userVote) || undefined;
    this.denyVoting = this.denyVoting !== false && (this.denyVoting as unknown) !== 'false';
  }

  vote(rating: number): void {
    this.voteService.send(this.elementId, rating, this.type).subscribe(newVotes => {
      this.overallRating = newVotes;
      this.currentUserVote = rating || undefined;
    });
  }
}
