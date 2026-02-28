import {
  ChangeDetectorRef,
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnDestroy,
  Output,
  SimpleChanges
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {Subscription} from 'rxjs';
import {ZxTuneDto} from '../../models/zx-tune-dto';
import {RatingComponent} from '../../components/rating/rating.component';
import {VoteService} from '../../services/vote.service';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {ZxPlaylistButtonComponent} from '../zx-playlist-button/zx-playlist-button.component';

@Component({
  selector: 'zx-tune-row',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    MatButtonModule,
    MatIconModule,
    RatingComponent,
    ZxBadgeComponent,
    ZxPlaylistButtonComponent,
  ],
  templateUrl: './zx-tune-row.component.html',
  styleUrls: ['./zx-tune-row.component.scss']
})
export class ZxTuneRowComponent implements OnChanges, OnDestroy {
  @Input() tune!: ZxTuneDto;
  @Input() index?: number;
  @Input() isPlaying = false;
  @Output() playRequested = new EventEmitter<ZxTuneDto>();
  @Output() pauseRequested = new EventEmitter<void>();

  realtimeBadgeLabel = '';

  private labelSub: Subscription | null = null;

  constructor(
    private voteService: VoteService,
    private cdr: ChangeDetectorRef,
    private translateService: TranslateService,
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['tune']) {
      this.updateRealtimeBadgeLabel();
    }
  }

  ngOnDestroy(): void {
    this.labelSub?.unsubscribe();
  }

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
    this.voteService.send<'zxMusic'>(this.tune.id, rating, 'zxMusic').subscribe(({votes, votesAmount}) => {
      this.tune = {...this.tune, votes, votesAmount, userVote: rating};
      this.cdr.detectChanges();
    });
  }

  requestPlay(): void {
    if (this.isPlaying) {
      this.pauseRequested.emit();
      return;
    }
    if (!this.tune.isPlayable || !this.tune.mp3Url) {
      return;
    }
    this.playRequested.emit(this.tune);
  }

  private updateRealtimeBadgeLabel(): void {
    this.labelSub?.unsubscribe();
    const key = this.tune?.compo ? `tune.compo.${this.tune.compo}` : 'firstpage.realtime';
    this.labelSub = this.translateService.stream(key).subscribe(translated => {
      this.realtimeBadgeLabel = this.tune?.compo && translated === key
        ? this.tune.compo
        : translated;
    });
  }
}
