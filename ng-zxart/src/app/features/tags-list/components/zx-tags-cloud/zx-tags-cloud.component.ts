import {
  ChangeDetectionStrategy,
  Component,
  inject,
  Input,
  OnChanges,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {RouterLink} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {
  distinctUntilChanged,
  map,
  Observable,
  ReplaySubject,
  scan,
  shareReplay,
  startWith,
  switchMap,
} from 'rxjs';
import {TagsListService} from '../../services/tags-list.service';
import {TagListItem} from '../../models/tag-list-item';
import {TagsListResult} from '../../models/tags-list-result';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxInsetComponent} from '../../../../shared/ui/zx-inset/zx-inset.component';
import {
  ZxLoadingStateDirective,
} from '../../../../shared/ui/zx-loading-state/zx-loading-state.directive';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {
  ZxTagsCloudSkeletonComponent,
} from '../zx-tags-cloud-skeleton/zx-tags-cloud-skeleton.component';

const DEFAULT_MINIMUM_AMOUNT = '10';
const TAG_FONT_SIZES = ['small', 'medium', 'large', 'extra-large'] as const;

type TagFontSize = typeof TAG_FONT_SIZES[number];

interface TagsCloudQuery {
  section: string;
  minimumAmount: number;
  reset: boolean;
}

interface TagsCloudLoadEvent {
  loading: boolean;
  reset: boolean;
  result?: TagsListResult;
}

interface TagsCloudVm {
  tags: TagListItem[] | null;
  loading: boolean;
  error: boolean;
  maxAmount: number;
}

const INITIAL_VM: TagsCloudVm = {
  tags: null,
  loading: true,
  error: false,
  maxAmount: 1,
};

/**
 * Tag cloud for a collection section (graphics/music). Each tag links to the
 * matching search entrypoint with the tag pre-applied (`tagsInclude`). Font size
 * scales with the tag's usage amount.
 */
@Component({
  selector: 'zx-tags-cloud',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterLink,
    TranslateModule,
    ZxInlineComponent,
    ZxInsetComponent,
    ZxLoadingStateDirective,
    ZxSelectComponent,
    ZxStackComponent,
    TextDirective,
    ZxTagsCloudSkeletonComponent,
  ],
  templateUrl: './zx-tags-cloud.component.html',
  styleUrls: ['./zx-tags-cloud.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTagsCloudComponent implements OnChanges {
  /** Collection section: 'graphics' or 'music'. */
  @Input() section = 'graphics';
  /** SPA search route the tags link to (e.g. '/pictures/search'). */
  @Input() searchBasePath = '/pictures/search';
  /** Search query parameter receiving the selected tag. */
  @Input() tagQueryParam = 'tagsInclude';
  /** Whether the search filter expects a tag title or numeric id. */
  @Input() tagQueryValue: 'id' | 'title' = 'title';

  selectedMinimumAmount = DEFAULT_MINIMUM_AMOUNT;

  private readonly tagsListService = inject(TagsListService);
  private readonly translateService = inject(TranslateService);
  private readonly query$ = new ReplaySubject<TagsCloudQuery>(1);

  readonly minimumAmountOptions$: Observable<ZxSelectOption[]> = this.translateService
    .stream('tags-cloud.allTags')
    .pipe(
      map(allTagsLabel => [
        {value: '1', label: String(allTagsLabel)},
        {value: '3', label: '3'},
        {value: '5', label: '5'},
        {value: '10', label: '10'},
      ]),
      shareReplay({bufferSize: 1, refCount: false}),
    );

  readonly vm$: Observable<TagsCloudVm> = this.query$.pipe(
    distinctUntilChanged((previous, current) =>
      previous.section === current.section && previous.minimumAmount === current.minimumAmount
    ),
    switchMap(query => this.tagsListService.getTags(query.section, query.minimumAmount).pipe(
      map(result => ({loading: false, reset: false, result}) satisfies TagsCloudLoadEvent),
      startWith({loading: true, reset: query.reset} satisfies TagsCloudLoadEvent),
    )),
    scan((vm, event): TagsCloudVm => {
      if (event.loading) {
        return event.reset ? {...INITIAL_VM} : {...vm, loading: true, error: false};
      }

      const tags = event.result?.items ?? [];
      return {
        tags,
        loading: false,
        error: event.result?.error ?? false,
        maxAmount: Math.max(1, ...tags.map(tag => tag.amount)),
      };
    }, INITIAL_VM),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  ngOnChanges(): void {
    this.requestTags(true);
  }

  onMinimumAmountChange(value: string): void {
    this.selectedMinimumAmount = value;
    this.requestTags(false);
  }

  fontSize(amount: number, maxAmount: number): TagFontSize {
    const index = Math.min(
      TAG_FONT_SIZES.length - 1,
      Math.floor(amount / maxAmount * TAG_FONT_SIZES.length),
    );
    return TAG_FONT_SIZES[index];
  }

  queryParamsFor(tag: TagListItem): Record<string, string> {
    return {
      [this.tagQueryParam]: this.tagQueryValue === 'id' ? String(tag.id) : tag.title,
    };
  }

  private requestTags(reset: boolean): void {
    this.query$.next({
      section: this.section,
      minimumAmount: Number(this.selectedMinimumAmount),
      reset,
    });
  }
}
