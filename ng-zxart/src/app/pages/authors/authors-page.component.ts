import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {combineLatest, map, Observable} from 'rxjs';
import {ZxAuthorBrowserComponent} from '../../features/author-browser/components/zx-author-browser/zx-author-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';

interface AuthorsVm {
  items: string;
  basePath: string;
  titleKey: string;
  letter: string;
}

/**
 * Routed author-listing page. One route per content type — `/authors` (all),
 * `/artists` (graphics), `/musicians` (music) — each carrying its own `items`
 * filter, base path, and heading via route data. All three reuse the shared
 * author browser (filters + table).
 */
@Component({
  selector: 'zx-authors-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxAuthorBrowserComponent,
    HeadingDirective,
  ],
  templateUrl: './authors-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorsPageComponent {
  readonly vm$: Observable<AuthorsVm> = combineLatest([this.route.data, this.route.paramMap]).pipe(
    map(([data, params]) => ({
      items: (data['items'] ?? '') as string,
      basePath: (data['basePath'] ?? '/authors') as string,
      titleKey: (data['titleKey'] ?? 'author-browser.title.all') as string,
      letter: params.get('letter') ?? '',
    })),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
