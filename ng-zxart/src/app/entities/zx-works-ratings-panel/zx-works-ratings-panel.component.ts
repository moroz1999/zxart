import {ChangeDetectionStrategy, Component, Input, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {AuthorRatingsListDto, RecentRatingDto} from '../../features/ratings/models/recent-rating.dto';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';
import {ZxUserComponent} from '../zx-user/zx-user.component';
import {ZxPaginationComponent} from '../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxRowSkeletonComponent} from '../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {InViewportDirective} from '../../shared/directives/in-viewport.directive';

/**
 * Shared "votes on works" panel: a paginated, lazy-loaded list of votes rendered with
 * zx-table + zx-user. Reused by the author and group discussion tabs; the host supplies
 * the title and a page loader so the panel stays element-agnostic.
 */
@Component({
  selector: 'zx-works-ratings-panel',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxTableComponent,
    ZxUserComponent,
    ZxPaginationComponent,
    ZxRowSkeletonComponent,
    TextDirective,
    InViewportDirective,
  ],
  templateUrl: './zx-works-ratings-panel.component.html',
  styleUrl: './zx-works-ratings-panel.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxWorksRatingsPanelComponent {
  @Input() title = '';
  @Input() loader!: (page: number) => Observable<AuthorRatingsListDto>;

  items = signal<RecentRatingDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);
  currentPage = signal(1);
  pagesAmount = signal(0);
  totalCount = signal(0);

  onInViewport(): void {
    if (!this.hasLoaded() && !this.loading()) {
      this.loadPage(1);
    }
  }

  onPageChange(page: number): void {
    this.loadPage(page);
  }

  private loadPage(page: number): void {
    if (!this.loader) {
      return;
    }
    this.loading.set(true);
    this.loader(page).subscribe(response => {
      this.items.set(response.items);
      this.currentPage.set(response.currentPage);
      this.pagesAmount.set(response.pagesAmount);
      this.totalCount.set(response.totalCount);
      this.loading.set(false);
      this.hasLoaded.set(true);
    });
  }
}
