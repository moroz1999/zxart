import {ChangeDetectorRef, Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
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
    MatButtonModule,
    MatIconModule,
    RatingComponent,
    ZxBadgeComponent,
  ],
  templateUrl: './zx-tune-row.component.html',
  styleUrls: ['./zx-tune-row.component.scss']
})
export class ZxTuneRowComponent {
  @Input() tune!: ZxTuneDto;
  @Input() index?: number;
  @Input() isPlaying = false;
  @Output() playRequested = new EventEmitter<ZxTuneDto>();
  @Output() pauseRequested = new EventEmitter<void>();

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
}
