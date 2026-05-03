import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {RatingsService} from '../../services/ratings.service';
import {RatingDto} from '../../models/rating.dto';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {
  ZxBodyDirective,
  ZxCaptionDirective,
  ZxHeading2Directive
} from '../../../../shared/directives/typography/typography.directives';
import {
  ZxRowSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';

@Component({
  selector: 'zx-ratings-list,zx-ratings-list-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxUserComponent,
    ZxTableComponent,
    ZxBodyDirective,
    ZxCaptionDirective,
    ZxHeading2Directive,
    ZxRowSkeletonComponent
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
