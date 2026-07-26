import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable} from 'rxjs';
import {
  ZxSearchResultsComponent,
} from '../../features/search-results/components/zx-search-results/zx-search-results.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

interface SearchPageVm {
  phrase: string;
  page: number;
}

@Component({
  selector: 'zx-search-page',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxSearchResultsComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './search-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchPageComponent {
  readonly vm$: Observable<SearchPageVm> = this.route.queryParamMap.pipe(
    map(params => ({
      phrase: params.get('phrase')?.trim() ?? '',
      page: Math.max(1, Number.parseInt(params.get('page') ?? '1', 10) || 1),
    })),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
