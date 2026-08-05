import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute, Router} from '@angular/router';
import {map, Observable} from 'rxjs';
import {childRouteParam} from '../../shared/utils/child-route-param';
import {ZxGroupDetailsComponent} from '../../features/group-details/components/zx-group-details/zx-group-details.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed page for `group/:id`. */
@Component({
  selector: 'zx-group-page',
  standalone: true,
  imports: [CommonModule, ZxGroupDetailsComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './group-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GroupPageComponent {
  title = '';

  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  /** Active tab lives on the `:tab` child route, absent on the default tab. */
  readonly tab$: Observable<string | null> = childRouteParam(this.route, this.router, 'tab');

  constructor(
    private readonly route: ActivatedRoute,
    private readonly router: Router,
  ) {}
}
