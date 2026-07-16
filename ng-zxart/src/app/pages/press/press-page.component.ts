import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxPressDetailsComponent} from '../../features/press-details/components/zx-press-details/zx-press-details.component';

/** Routed page for `press/:id`. */
@Component({
  selector: 'zx-press-page',
  standalone: true,
  imports: [CommonModule, ZxPressDetailsComponent],
  templateUrl: './press-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PressPageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
