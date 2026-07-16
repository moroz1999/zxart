import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxReleaseDetailsComponent} from '../../features/release-details/components/zx-release-details/zx-release-details.component';

/** Routed page for `release/:id`. */
@Component({
  selector: 'zx-release-page',
  standalone: true,
  imports: [CommonModule, ZxReleaseDetailsComponent],
  templateUrl: './release-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReleasePageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
