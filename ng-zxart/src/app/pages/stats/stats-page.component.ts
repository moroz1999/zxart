import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxStatsComponent} from '../../features/stats/components/zx-stats/zx-stats.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed statistics entrypoint (`/stats`). */
@Component({
  selector: 'zx-stats-page',
  standalone: true,
  imports: [TranslateModule, ZxStatsComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './stats-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StatsPageComponent {}
