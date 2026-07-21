import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, map, Observable} from 'rxjs';
import {ZxPictureBrowserComponent} from '../../features/picture-browser/components/zx-picture-browser/zx-picture-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxNavChip} from '../../shared/ui/zx-nav-chips/nav-chip';
import {ZxNavChipsComponent} from '../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

const TOP_PICTURES_PATH = '/pictures/top';

/**
 * Subsets of the top graphics, selected by tag. The game-related ones use the
 * "Loading Screen" and "Game Graphics" tag elements; `tagId` 0 is the whole
 * collection.
 */
const TOP_PICTURES_FILTERS: readonly {readonly labelKey: string; readonly tagId: number}[] = [
  {labelKey: 'top-pictures.filter.all', tagId: 0},
  {labelKey: 'top-pictures.filter.loading-screens', tagId: 46245},
  {labelKey: 'top-pictures.filter.game-graphics', tagId: 47883},
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
    this.route.queryParams,
    this.translateService.stream(TOP_PICTURES_FILTERS.map(filter => filter.labelKey)),
  ]).pipe(
    map(([params, labels]) => {
      const activeTagId = params['tag'] ? +params['tag'] : 0;
      return TOP_PICTURES_FILTERS.map(filter => ({
        label: labels[filter.labelKey] ?? filter.labelKey,
        href: filter.tagId > 0 ? `${TOP_PICTURES_PATH}?tag=${filter.tagId}` : TOP_PICTURES_PATH,
        active: filter.tagId === activeTagId,
      }));
    }),
  );

  constructor(
    private readonly route: ActivatedRoute,
    private readonly translateService: TranslateService,
  ) {}
}
