import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {ChartConfiguration, ChartData} from 'chart.js';
import {BaseChartDirective} from 'ng2-charts';

export interface StatsBarDataset {
  label: string;
  data: number[];
  color: string;
}

@Component({
  selector: 'zx-stats-bar-chart',
  standalone: true,
  imports: [BaseChartDirective],
  templateUrl: './zx-stats-bar-chart.component.html',
  styleUrl: './zx-stats-bar-chart.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsBarChartComponent implements OnChanges {
  @Input() labels: string[] = [];
  @Input() datasets: StatsBarDataset[] = [];
  @Input() stacked = false;
  @Input() percentage = false;
  @Input() height = 200;

  chartData: ChartData<'bar'> = {labels: [], datasets: []};
  chartOptions: ChartConfiguration<'bar'>['options'] = {};

  ngOnChanges(): void {
    const data = this.percentage ? this.toPercentages() : this.datasets.map(dataset => dataset.data);

    this.chartData = {
      labels: this.labels,
      datasets: this.datasets.map((dataset, index) => ({
        label: dataset.label,
        data: data[index],
        backgroundColor: dataset.color,
        hoverBackgroundColor: dataset.color,
        borderWidth: 0,
        borderRadius: 2,
        borderSkipped: false,
        barPercentage: 0.96,
        categoryPercentage: 0.96,
        stack: this.stacked ? 'stack' : String(index),
      })),
    };

    this.chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      animation: false,
      interaction: {mode: 'index', intersect: false},
      plugins: {
        legend: {display: false},
        tooltip: {
          callbacks: this.percentage
            ? {label: context => `${context.dataset.label}: ${Math.round(context.parsed.y ?? 0)}%`}
            : {},
        },
      },
      scales: {
        x: {
          stacked: this.stacked,
          grid: {display: false},
          ticks: {maxRotation: 0, autoSkipPadding: 16, font: {size: 10}},
        },
        y: {
          stacked: this.stacked,
          beginAtZero: true,
          max: this.percentage ? 100 : undefined,
          grid: {color: 'rgba(127,127,127,0.15)'},
          ticks: {font: {size: 10}, precision: 0},
        },
      },
    };
  }

  private toPercentages(): number[][] {
    const totals = this.labels.map((_, index) =>
      this.datasets.reduce((sum, dataset) => sum + (dataset.data[index] ?? 0), 0),
    );

    return this.datasets.map(dataset =>
      dataset.data.map((value, index) => (totals[index] > 0 ? (value / totals[index]) * 100 : 0)),
    );
  }
}
