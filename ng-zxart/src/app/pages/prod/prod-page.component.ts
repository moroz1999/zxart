import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxProdDetailsComponent} from '../../features/prod-details/components/zx-prod-details/zx-prod-details.component';

/** Routed page for `prod/:id`. */
@Component({
  selector: 'zx-prod-page',
  standalone: true,
  imports: [CommonModule, ZxProdDetailsComponent],
  templateUrl: './prod-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProdPageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  readonly tab$: Observable<string | null> = this.route.paramMap.pipe(
    map(params => params.get('tab')),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
