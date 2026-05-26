import {ChangeDetectionStrategy, Component, Input, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {RatingsService} from '../../../ratings/services/ratings.service';
import {RecentRatingDto} from '../../../ratings/models/recent-rating.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxTextSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-text-skeleton/zx-text-skeleton.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

@Component({
  selector: 'zx-author-ratings',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxTableComponent,
    ZxUserComponent,
    ZxPaginationComponent,
    ZxTextSkeletonComponent,
    TextDirective,
  ],
  templateUrl: './zx-author-ratings.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorRatingsComponent implements OnInit {
  @Input() elementId = 0;

  items = signal<RecentRatingDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);
  currentPage = signal(1);
  pagesAmount = signal(0);
  totalCount = signal(0);

  constructor(private ratingsService: RatingsService) {}

  ngOnInit(): void {
    this.loadPage(1);
  }

  onPageChange(page: number): void {
    this.loadPage(page);
  }

  private loadPage(page: number): void {
    if (!this.elementId) {
      return;
    }
    this.loading.set(true);
    this.ratingsService.getAuthorRatings(this.elementId, page).subscribe(response => {
      this.items.set(response.items);
      this.currentPage.set(response.currentPage);
      this.pagesAmount.set(response.pagesAmount);
      this.totalCount.set(response.totalCount);
      this.loading.set(false);
      this.hasLoaded.set(true);
    });
  }
}
