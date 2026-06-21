import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {ChartConfiguration, ChartData} from 'chart.js';
import {BaseChartDirective} from 'ng2-charts';

type StatsChartHeight = 'sm' | 'md' | 'lg';

@Component({
  selector: 'zx-stats-line-chart',
  standalone: true,
  imports: [BaseChartDirective],
  templateUrl: './zx-stats-line-chart.component.html',
  styleUrl: './zx-stats-line-chart.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsLineChartComponent implements OnChanges {
  @Input() labels: string[] = [];
  @Input() data: number[] = [];
  @Input() color = '';
  @Input() fill = '';
  @Input() min = 2.5;
  @Input() max = 5;
  @Input() height: StatsChartHeight = 'lg';

  chartData: ChartData<'line'> = {labels: [], datasets: []};
  chartOptions: ChartConfiguration<'line'>['options'] = {};

  get heightClass(): string {
    return `zx-stats-line-chart__canvas zx-stats-line-chart__canvas--${this.height}`;
  }

  ngOnChanges(): void {
    const tickFontSize = this.cssNumber('--zx-stats-chart-tick-font-size');
    const tickAutoSkipPadding = this.cssNumber('--zx-stats-chart-tick-auto-skip-padding');
    const gridColor = this.cssValue('--zx-stats-chart-grid-color');

    this.chartData = {
      labels: this.labels,
      datasets: [
        {
          data: this.data,
          borderColor: this.color,
          backgroundColor: this.fill,
          pointBackgroundColor: this.color,
          pointRadius: 0,
          pointHoverRadius: 4,
          borderWidth: 2,
          tension: 0.25,
          fill: true,
        },
      ],
    };

    this.chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      interaction: {mode: 'index', intersect: false},
      plugins: {
        legend: {display: false},
        tooltip: {callbacks: {label: context => (context.parsed.y ?? 0).toFixed(1)}},
      },
      scales: {
        x: {grid: {display: false}, ticks: {maxRotation: 0, autoSkipPadding: tickAutoSkipPadding, font: {size: tickFontSize}}},
        y: {
          min: this.min,
          max: this.max,
          grid: {color: gridColor},
          ticks: {font: {size: tickFontSize}, stepSize: 0.5},
        },
      },
    };
  }

  private cssValue(name: string): string {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  }

  private cssNumber(name: string): number {
    return Number(this.cssValue(name));
  }
}
