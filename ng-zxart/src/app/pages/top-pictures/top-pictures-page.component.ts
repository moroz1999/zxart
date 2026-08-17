import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, map, Observable} from 'rxjs';
import {ZxPictureBrowserComponent} from '../../features/picture-browser/components/zx-picture-browser/zx-picture-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {buildFilterChips, filterChipKey, FilterSlug, ZxNavChip} from '../../shared/ui/zx-nav-chips/nav-chip';
import {ZxNavChipsComponent} from '../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {childRouteParam} from '../../shared/utils/child-route-param';

const TOP_PICTURES_PATH = '/pictures/top';
const FILTER_LABEL_PREFIX = 'top-pictures.filter';

/**
 * Subsets of the top graphics, named by the trailing route segment the chip
 * links to. The backend resolves each slug to its tag or its picture formats
 * (`PictureCollectionFilter`); `null` is the whole collection.
 */
const TOP_PICTURES_FILTERS: readonly FilterSlug[] = [
  null,
  'loading',
  'ingame',
  'nocopy',
  'gigascreen',
  'samcoupe',
  'next',
];

@Component({
  selector: 'zx-top-pictures-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureBrowserComponent,
    HeadingDirective,
    ZxNavChipsComponent,
    ZxPageLayoutComponent,
  ],
  templateUrl: './top-pictures-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TopPicturesPageComponent {
  readonly chips$: Observable<ZxNavChip[]> = combineLatest([
    childRouteParam(this.route, this.router, 'filter'),
    this.translateService.stream(TOP_PICTURES_FILTERS.map(slug => filterChipKey(FILTER_LABEL_PREFIX, slug))),
  ]).pipe(
    map(([activeSlug, labels]) => buildFilterChips(
      TOP_PICTURES_PATH,
      TOP_PICTURES_FILTERS,
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
