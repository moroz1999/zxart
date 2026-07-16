import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxTuneDetailsComponent} from '../../features/tune-details/components/zx-tune-details/zx-tune-details.component';

/** Routed page for `tune/:id`. */
@Component({
  selector: 'zx-tune-page',
  standalone: true,
  imports: [CommonModule, ZxTuneDetailsComponent],
  templateUrl: './tune-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TunePageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
