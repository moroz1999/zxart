import {ChangeDetectionStrategy, Component, Input, OnChanges, OnDestroy} from '@angular/core';
import {AsyncPipe} from '@angular/common';
import {BehaviorSubject, Observable, Subscription} from 'rxjs';
import {tap} from 'rxjs/operators';
import {RatingComponent} from '../../components/rating/rating.component';
import {ZxPlaylistButtonComponent} from '../zx-playlist-button/zx-playlist-button.component';
import {VoteService} from '../../services/vote.service';

interface VoteState {
  overallRating: number;
  votesAmount: number;
  userRating: number | undefined;
}

@Component({
  selector: 'zx-item-controls',
  standalone: true,
  imports: [RatingComponent, ZxPlaylistButtonComponent, AsyncPipe],
  templateUrl: './zx-item-controls.component.html',
  styleUrls: ['./zx-item-controls.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxItemControlsComponent implements OnChanges, OnDestroy {
  @Input() elementId!: number;
  @Input() type!: string;
  @Input() votes: number = 0;
  @Input() votesAmount: number = 0;
  @Input() userRating?: number | null = null;
  @Input() denyVoting: boolean = false;

  private readonly stateStore = new BehaviorSubject<VoteState>({
    overallRating: 0,
    votesAmount: 0,
    userRating: undefined,
  });
  readonly state$: Observable<VoteState> = this.stateStore.asObservable();

  private readonly subscription = new Subscription();

  constructor(private voteService: VoteService) {}

  ngOnChanges(): void {
    this.stateStore.next({
      overallRating: this.votes,
      votesAmount: this.votesAmount,
      userRating: this.userRating ?? undefined,
    });
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  onVote(star: number): void {
    this.subscription.add(
      this.voteService.send(this.elementId, star, this.type).pipe(
        tap(result => this.stateStore.next({
          overallRating: result.votes,
          votesAmount: result.votesAmount,
          userRating: star || undefined,
        })),
      ).subscribe({error: () => {}}),
    );
  }
}
