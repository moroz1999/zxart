import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxGroupDetailsComponent} from '../../features/group-details/components/zx-group-details/zx-group-details.component';

/** Routed page for `group/:id`. */
@Component({
  selector: 'zx-group-page',
  standalone: true,
  imports: [CommonModule, ZxGroupDetailsComponent],
  templateUrl: './group-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GroupPageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  readonly tab$: Observable<string | null> = this.route.paramMap.pipe(
    map(params => params.get('tab')),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
