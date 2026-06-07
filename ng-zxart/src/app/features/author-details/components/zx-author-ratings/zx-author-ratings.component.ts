import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {RatingsService} from '../../../ratings/services/ratings.service';
import {AuthorRatingsListDto} from '../../../ratings/models/recent-rating.dto';
import {ZxWorksRatingsPanelComponent} from '../../../../entities/zx-works-ratings-panel/zx-works-ratings-panel.component';

@Component({
  selector: 'zx-author-ratings',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxWorksRatingsPanelComponent],
  template: `
    <zx-works-ratings-panel
      [title]="'author.votes-on-works' | translate"
      [loader]="loader">
    </zx-works-ratings-panel>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorRatingsComponent {
  @Input() elementId = 0;

  constructor(private readonly ratingsService: RatingsService) {}

  loader = (page: number): Observable<AuthorRatingsListDto> =>
    this.ratingsService.getAuthorRatings(this.elementId, page);
}
