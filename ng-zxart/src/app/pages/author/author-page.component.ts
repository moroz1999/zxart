import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxAuthorDetailsComponent} from '../../features/author-details/components/zx-author-details/zx-author-details.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed page for `author/:id`. */
@Component({
  selector: 'zx-author-page',
  standalone: true,
  imports: [CommonModule, ZxAuthorDetailsComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './author-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AuthorPageComponent {
  title = '';

  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  readonly tab$: Observable<string | null> = this.route.paramMap.pipe(
    map(params => params.get('tab')),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
