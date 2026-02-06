import {Component, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {RatingsService} from '../../services/ratings.service';
import {RecentRatingDto} from '../../models/recent-rating.dto';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxLinkDirective} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'zx-recent-ratings',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxSkeletonComponent,
    ZxUserComponent,
    ZxLinkDirective
  ],
  templateUrl: './recent-ratings-widget.component.html',
  styleUrls: ['./recent-ratings-widget.component.scss']
})
export class RecentRatingsWidgetComponent implements OnInit {
  items = signal<RecentRatingDto[]>([]);
  loading = signal(true);

  constructor(private ratingsService: RatingsService) {}

  ngOnInit(): void {
    this.ratingsService.getRecentRatings(20).subscribe({
      next: (items) => {
        this.items.set(items);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
      }
    });
  }
}
