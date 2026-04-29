import {ChangeDetectionStrategy, Component, Input, OnChanges, OnDestroy} from '@angular/core';
import {AsyncPipe} from '@angular/common';
import {BehaviorSubject, Observable, Subscription} from 'rxjs';
import {tap} from 'rxjs/operators';
import {RatingComponent} from '../../components/rating/rating.component';
import {VoteService} from '../../services/vote.service';

interface VoteState {
  overallRating: number;
  votesAmount: number;
  userRating: number | undefined;
}

/**
 * @deprecated Use `zx-item-legacy-controls` in Smarty templates instead.
 */
@Component({
  selector: 'zx-vote',
  standalone: true,
  imports: [RatingComponent, AsyncPipe],
  templateUrl: './zx-vote.component.html',
  styleUrls: ['./zx-vote.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxVoteComponent implements OnChanges, OnDestroy {
  @Input({alias: 'element-id'}) elementId!: number;
  @Input() type!: string;
  @Input() votes: number = 0;
  @Input({alias: 'votes-amount'}) votesAmount: number = 0;
  @Input({alias: 'user-vote'}) userVote: number = 0;
  @Input({alias: 'deny-voting'}) denyVoting: boolean | string = false;

  private readonly stateStore = new BehaviorSubject<VoteState>({
    overallRating: 0,
    votesAmount: 0,
    userRating: undefined,
  });
  readonly state$: Observable<VoteState> = this.stateStore.asObservable();

  private readonly subscription = new Subscription();

  constructor(private voteService: VoteService) {}

  get isVotingDenied(): boolean {
    return this.denyVoting !== false && (this.denyVoting as unknown) !== 'false';
  }

  ngOnChanges(): void {
    this.stateStore.next({
      overallRating: Number(this.votes) || 0,
      votesAmount: Number(this.votesAmount) || 0,
      userRating: Number(this.userVote) || undefined,
    });
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  onVote(star: number): void {
    this.subscription.add(
      this.voteService.send(Number(this.elementId), star, this.type).pipe(
        tap(result => this.stateStore.next({
          overallRating: result.votes,
          votesAmount: result.votesAmount,
          userRating: star || undefined,
        })),
      ).subscribe({error: () => {}}),
    );
  }
}
