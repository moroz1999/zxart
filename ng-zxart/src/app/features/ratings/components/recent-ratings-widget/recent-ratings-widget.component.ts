import {ChangeDetectionStrategy, Component, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {RatingsService} from '../../services/ratings.service';
import {RecentRatingDto} from '../../models/recent-rating.dto';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxLinkDirective} from '../../../../shared/directives/typography/typography.directives';

const PAGE_SIZE = 20;

@Component({
  selector: 'zx-recent-ratings',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxSkeletonComponent,
    ZxSpinnerComponent,
    ZxUserComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxLinkDirective,
  ],
  templateUrl: './recent-ratings-widget.component.html',
  styleUrls: ['./recent-ratings-widget.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RecentRatingsWidgetComponent implements OnInit {
  items = signal<RecentRatingDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);
  offset = signal(0);
  hasMore = signal(false);

  constructor(private ratingsService: RatingsService) {}

  ngOnInit(): void {
    this.loadPage(0);
  }

  prevPage(): void {
    this.loadPage(Math.max(0, this.offset() - PAGE_SIZE));
  }

  nextPage(): void {
    this.loadPage(this.offset() + PAGE_SIZE);
  }

  private loadPage(offset: number): void {
    this.loading.set(true);
    this.ratingsService.getRecentRatings(PAGE_SIZE, offset).subscribe(response => {
      this.items.set(response.items);
      this.hasMore.set(response.hasMore);
      this.offset.set(offset);
      this.loading.set(false);
      this.hasLoaded.set(true);
    });
  }
}
