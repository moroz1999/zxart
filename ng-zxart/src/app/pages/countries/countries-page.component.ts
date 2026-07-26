import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxGeoComponent} from '../../features/geo/components/zx-geo/zx-geo.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed countries/geography entrypoint (`/geo`). */
@Component({
  selector: 'zx-countries-page',
  standalone: true,
  imports: [TranslateModule, ZxGeoComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './countries-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CountriesPageComponent {}
