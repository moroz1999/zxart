import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {RatingsService} from '../../services/ratings.service';
import {RatingDto} from '../../models/rating.dto';
import {ViewportLoaderComponent} from '../../../../shared/components/viewport-loader/viewport-loader.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxTableDirective} from '../../../../shared/directives/table/table.directive';
import {
  ZxBodyDirective,
  ZxCaptionDirective,
  ZxHeading2Directive
} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'zx-ratings-list',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ViewportLoaderComponent,
    ZxUserComponent,
    ZxTableDirective,
    ZxBodyDirective,
    ZxCaptionDirective,
    ZxHeading2Directive
  ],
  templateUrl: './ratings-list.component.html',
  styleUrls: ['./ratings-list.component.scss']
})
export class RatingsListComponent {
  @Input() elementId?: number;

  constructor(private ratingsService: RatingsService) {}

  getRatingsLoader = (): Observable<RatingDto[]> => {
    if (this.elementId) {
      return this.ratingsService.getRatings(this.elementId);
    }
    return of([]);
  };
}
