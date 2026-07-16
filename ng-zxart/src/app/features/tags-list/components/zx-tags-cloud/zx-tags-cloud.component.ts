import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  Input,
  OnChanges,
  OnDestroy,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {TagsListService} from '../../services/tags-list.service';
import {TagListItem} from '../../models/tag-list-item';

const MIN_FONT_EM = 1;
const MAX_FONT_EM = 4;

/**
 * Tag cloud for a collection section (graphics/music). Each tag links to the
 * matching search entrypoint with the tag pre-applied (`tagsInclude`). Font size
 * scales with the tag's usage amount.
 */
@Component({
  selector: 'zx-tags-cloud',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslateModule],
  templateUrl: './zx-tags-cloud.component.html',
  styleUrls: ['./zx-tags-cloud.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTagsCloudComponent implements OnChanges, OnDestroy {
  /** Collection section: 'graphics' or 'music'. */
  @Input() section = 'graphics';
  /** SPA search route the tags link to (e.g. '/pictures/search'). */
  @Input() searchBasePath = '/pictures/search';

  tags: TagListItem[] = [];
  loading = true;
  error = false;

  private maxAmount = 1;
  private subscription?: Subscription;

  constructor(
    private readonly tagsListService: TagsListService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnChanges(): void {
    this.load();
  }

  ngOnDestroy(): void {
    this.subscription?.unsubscribe();
  }

  fontSizeEm(amount: number): number {
    return MIN_FONT_EM + (MAX_FONT_EM - MIN_FONT_EM) * (amount - 1) / this.maxAmount;
  }

  queryParamsFor(tag: TagListItem): Record<string, string> {
    return {tagsInclude: tag.title};
  }

  private load(): void {
    this.loading = true;
    this.error = false;
    this.subscription?.unsubscribe();
    this.subscription = this.tagsListService.getTags(this.section).subscribe({
      next: tags => {
        this.tags = tags;
        this.maxAmount = Math.max(1, ...tags.map(tag => tag.amount));
        this.loading = false;
        this.cdr.markForCheck();
      },
      error: () => {
        this.error = true;
        this.loading = false;
        this.cdr.markForCheck();
      },
    });
  }
}
