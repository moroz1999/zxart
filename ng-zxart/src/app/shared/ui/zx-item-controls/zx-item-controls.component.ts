import {Component, Input, OnInit} from '@angular/core';
import {RatingComponent} from '../../components/rating/rating.component';
import {ZxPlaylistButtonComponent} from '../zx-playlist-button/zx-playlist-button.component';
import {VoteService} from '../../services/vote.service';

/**
 * Unified item controls component — vote rating + playlist (favourites) button in one row.
 *
 * Used in both Angular components (typed bindings) and legacy Smarty templates (string HTML attributes).
 * Manages its own vote state internally — parent components do not need to handle vote responses.
 *
 * Angular usage:
 *   <zx-item-controls [elementId]="item.id" type="zxPicture"
 *     [votes]="item.votes" [votesAmount]="item.votesAmount"
 *     [userVote]="item.userVote ?? 0" [denyVoting]="item.denyVoting">
 *   </zx-item-controls>
 *
 * Legacy Smarty usage (custom element):
 *   <zx-item-controls element-id="{$element->id}" type="zxPicture"
 *     votes="{$element->votes}" votes-amount="{$element->votesAmount}"
 *     user-vote="{$element->getUserVote()}"
 *     deny-voting="{if $element->isVotingDenied()}true{else}false{/if}">
 *   </zx-item-controls>
 */
@Component({
  selector: 'zx-item-controls',
  standalone: true,
  imports: [RatingComponent, ZxPlaylistButtonComponent],
  templateUrl: './zx-item-controls.component.html',
  styleUrls: ['./zx-item-controls.component.scss'],
})
export class ZxItemControlsComponent implements OnInit {
  @Input() elementId!: number;
  @Input() type!: string;
  @Input() votes: number = 0;
  @Input() votesAmount: number = 0;
  @Input() userVote: number = 0;
  @Input() denyVoting: boolean = false;

  currentVotes: number = 0;
  currentVotesAmount: number = 0;
  currentUserVote: number | undefined;

  constructor(private voteService: VoteService) {}

  ngOnInit(): void {
    this.elementId = Number(this.elementId);
    this.currentVotes = Number(this.votes) || 0;
    this.currentVotesAmount = Number(this.votesAmount) || 0;
    this.currentUserVote = Number(this.userVote) || undefined;
    this.denyVoting = this.denyVoting !== false && (this.denyVoting as unknown) !== 'false';
  }

  vote(rating: number): void {
    this.voteService.send(this.elementId, rating, this.type).subscribe(result => {
      this.currentVotes = result.votes;
      this.currentVotesAmount = result.votesAmount;
      this.currentUserVote = rating || undefined;
    });
  }
}
