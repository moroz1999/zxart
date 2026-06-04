import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {RatingsService} from '../../services/ratings.service';
import {RatingDto} from '../../models/rating.dto';
import {ZxUserComponent} from '../../../../entities/zx-user/zx-user.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {
  ZxRowSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-ratings-list,zx-ratings-list-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxUserComponent,
    ZxTableComponent,
    TextDirective,
    HeadingDirective,
    ZxRowSkeletonComponent,
    ZxStackComponent,
  ],
  templateUrl: './ratings-list.component.html',
  styleUrls: ['./ratings-list.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RatingsListComponent implements OnChanges {
  @Input() elementId?: number;

  ratings$: Observable<RatingDto[] | null> = of(null);

  constructor(private ratingsService: RatingsService) {}

  ngOnChanges(): void {
    this.ratings$ = this.elementId ? this.ratingsService.getRatings(this.elementId) : of([]);
  }
}
