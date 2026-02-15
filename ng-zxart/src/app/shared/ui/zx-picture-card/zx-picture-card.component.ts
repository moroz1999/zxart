import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPictureDto} from '../../models/zx-picture-dto';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {RatingComponent} from '../../components/rating/rating.component';
import {VoteService} from '../../services/vote.service';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';
import {LightboxModule} from 'ng-gallery/lightbox';
import {ZxPlaylistButtonComponent} from '../zx-playlist-button/zx-playlist-button.component';

@Component({
  selector: 'zx-picture-card',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxBadgeComponent,
    RatingComponent,
    ZxCaptionDirective,
    LightboxModule,
    ZxPlaylistButtonComponent,
  ],
  templateUrl: './zx-picture-card.component.html',
  styleUrls: ['./zx-picture-card.component.scss']
})
export class ZxPictureCardComponent {
  @Input() picture!: ZxPictureDto;
  @Input() galleryIndex: number | null = null;
  @Input() galleryId: string | null = null;

  readonly defaultGalleryId = 'zx-picture-lightbox-default';

  constructor(private voteService: VoteService) {}

  get authorsText(): string {
    return this.picture.authors.map(a => a.name).join(', ');
  }

  get medalClass(): string | null {
    if (!this.picture.party?.place) return null;
    switch (this.picture.party.place) {
      case 1: return 'medal-gold';
      case 2: return 'medal-silver';
      case 3: return 'medal-bronze';
      default: return null;
    }
  }

  vote(rating: number): void {
    this.voteService.send<'zxPicture'>(this.picture.id, rating, 'zxPicture').subscribe();
  }
}
