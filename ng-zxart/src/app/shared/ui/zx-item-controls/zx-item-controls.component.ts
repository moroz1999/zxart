import {Component, Input} from '@angular/core';
import {RatingComponent} from '../../components/rating/rating.component';
import {ZxPlaylistButtonComponent} from '../zx-playlist-button/zx-playlist-button.component';

/**
 * Unified item controls component — vote rating + playlist (favourites) button in one row.
 */
@Component({
  selector: 'zx-item-controls',
  standalone: true,
  imports: [RatingComponent, ZxPlaylistButtonComponent],
  templateUrl: './zx-item-controls.component.html',
  styleUrls: ['./zx-item-controls.component.scss'],
})
export class ZxItemControlsComponent {
  @Input() elementId!: number;
  @Input() type!: string;
  @Input() votes: number = 0;
  @Input() votesAmount: number = 0;
  @Input() userRating?: number | null = null;
  @Input() denyVoting: boolean = false;
}
