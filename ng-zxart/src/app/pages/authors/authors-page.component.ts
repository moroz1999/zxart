import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {combineLatest, map, Observable} from 'rxjs';
import {childRouteParam} from '../../shared/utils/child-route-param';
import {ZxAuthorBrowserComponent} from '../../features/author-browser/components/zx-author-browser/zx-author-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxAuthorsDashboardComponent} from '../../features/authors-page/components/zx-authors-dashboard/zx-authors-dashboard.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxEditButtonComponent} from '../../shared/ui/zx-edit-button/zx-edit-button.component';
import {CurrentUserService} from '../../shared/services/current-user.service';
import {ZxInlineComponent} from '../../shared/ui/zx-inline/zx-inline.component';

interface AuthorsVm {
  items: '' | 'graphics' | 'music';
  basePath: string;
  titleKey: string;
  letter: string;
  dashboard: boolean;
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
    ZxAuthorsDashboardComponent,
    ZxEditButtonComponent,
    ZxInlineComponent,
    ZxPageLayoutComponent,
  ],
  templateUrl: './authors-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorsPageComponent {
  readonly isAuthenticated$ = this.currentUserService.isAuthenticated$;
  readonly vm$: Observable<AuthorsVm> = combineLatest([
    this.route.data,
    childRouteParam(this.route, this.router, 'letter'),
  ]).pipe(
    map(([data, letter]) => ({
      items: (data['items'] ?? '') as '' | 'graphics' | 'music',
      basePath: (data['basePath'] ?? '/authors') as string,
      titleKey: (data['titleKey'] ?? 'author-browser.title.all') as string,
      letter: letter ?? '',
      // the dashboard is the section root; picking a letter switches to the browser
      dashboard: data['dashboard'] === true && letter === null,
    })),
  );

  constructor(
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly currentUserService: CurrentUserService,
  ) {}

  authorAddUrl(vm: AuthorsVm): string {
    return vm.letter ? `/authors/${encodeURIComponent(vm.letter)}/add` : '/authors/add';
  }
}
