import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxPartyDetailsComponent} from '../../features/party-details/components/zx-party-details/zx-party-details.component';

/** Routed page for `party/:id`. */
@Component({
  selector: 'zx-party-page',
  standalone: true,
  imports: [CommonModule, ZxPartyDetailsComponent],
  templateUrl: './party-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PartyPageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  readonly tab$: Observable<string | null> = this.route.paramMap.pipe(
    map(params => params.get('tab')),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
