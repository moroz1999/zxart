import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxProdDetailsComponent} from '../../features/prod-details/components/zx-prod-details/zx-prod-details.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed page for `prod/:id`. */
@Component({
  selector: 'zx-prod-page',
  standalone: true,
  imports: [CommonModule, ZxProdDetailsComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './prod-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProdPageComponent {
  title = '';

  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  readonly tab$: Observable<string | null> = this.route.paramMap.pipe(
    map(params => params.get('tab')),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
