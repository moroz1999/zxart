import {Routes} from '@angular/router';
import {provideCharts, withDefaultRegisterables} from 'ng2-charts';
import {StatsPageComponent} from './stats-page.component';

/**
 * Stats is loaded as its own chunk. Chart.js registerables are provided at the
 * route level (not globally) so ng2-charts and chart.js ship inside this chunk
 * instead of the initial bundle.
 */
export const STATS_ROUTES: Routes = [
  {
    path: '',
    component: StatsPageComponent,
    providers: [provideCharts(withDefaultRegisterables())],
  },
];
