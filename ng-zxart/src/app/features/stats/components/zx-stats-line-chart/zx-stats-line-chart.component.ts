import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {ChartConfiguration, ChartData} from 'chart.js';
import {BaseChartDirective} from 'ng2-charts';

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
  @Input() color = '#000';
  @Input() fill = 'rgba(0,0,0,0.1)';
  @Input() min = 2.5;
  @Input() max = 5;
  @Input() height = 200;

  chartData: ChartData<'line'> = {labels: [], datasets: []};
  chartOptions: ChartConfiguration<'line'>['options'] = {};

  ngOnChanges(): void {
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
        x: {grid: {display: false}, ticks: {maxRotation: 0, autoSkipPadding: 16, font: {size: 10}}},
        y: {
          min: this.min,
          max: this.max,
          grid: {color: 'rgba(127,127,127,0.15)'},
          ticks: {font: {size: 10}, stepSize: 0.5},
        },
      },
    };
  }
}
