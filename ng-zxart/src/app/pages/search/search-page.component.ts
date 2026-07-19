import {ChangeDetectionStrategy, Component} from '@angular/core';
import {
  ZxSearchResultsComponent,
} from '../../features/search-results/components/zx-search-results/zx-search-results.component';

@Component({
  selector: 'zx-search-page',
  standalone: true,
  imports: [ZxSearchResultsComponent],
  template: '<zx-search-results [manageUrl]="false"></zx-search-results>',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SearchPageComponent {}
