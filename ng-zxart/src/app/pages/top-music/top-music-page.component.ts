import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, map, Observable} from 'rxjs';
import {ZxMusicBrowserComponent} from '../../features/music-browser/components/zx-music-browser/zx-music-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {buildFilterChips, filterChipKey, FilterSlug, ZxNavChip} from '../../shared/ui/zx-nav-chips/nav-chip';
import {ZxNavChipsComponent} from '../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {childRouteParam} from '../../shared/utils/child-route-param';

const TOP_MUSIC_PATH = '/music/top';
const FILTER_LABEL_PREFIX = 'top-music.filter';

/**
 * Subsets of the top music, named by the trailing route segment the chip links
 * to. The backend resolves each slug to its tag, its format groups or its game
 * link (`MusicCollectionFilter`); `null` is the whole collection.
 */
const TOP_MUSIC_FILTERS: readonly FilterSlug[] = [
  null,
  'cover',
  'original',
  'ay',
  'beeper',
  'digitalay',
  'samcoupe',
  'turbosound',
  'fm',
  'games',
];

@Component({
  selector: 'zx-top-music-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxMusicBrowserComponent,
    HeadingDirective,
    ZxNavChipsComponent,
    ZxPageLayoutComponent,
  ],
  templateUrl: './top-music-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TopMusicPageComponent {
  readonly chips$: Observable<ZxNavChip[]> = combineLatest([
    childRouteParam(this.route, this.router, 'filter'),
    this.translateService.stream(TOP_MUSIC_FILTERS.map(slug => filterChipKey(FILTER_LABEL_PREFIX, slug))),
  ]).pipe(
    map(([activeSlug, labels]) => buildFilterChips(
      TOP_MUSIC_PATH,
      TOP_MUSIC_FILTERS,
      FILTER_LABEL_PREFIX,
      labels,
      activeSlug,
    )),
  );

  constructor(
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly translateService: TranslateService,
  ) {}
}
