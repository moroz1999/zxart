import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ZxGeoComponent} from '../../features/geo/components/zx-geo/zx-geo.component';

/** Routed countries/geography entrypoint (`/geo`). */
@Component({
  selector: 'zx-countries-page',
  standalone: true,
  imports: [ZxGeoComponent],
  template: '<zx-geo></zx-geo>',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CountriesPageComponent {}
