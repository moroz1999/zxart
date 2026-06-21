import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {ChartConfiguration, ChartData} from 'chart.js';
import {BaseChartDirective} from 'ng2-charts';

export interface StatsBarDataset {
  label: string;
  data: number[];
  color: string;
  colorClass?: string;
}

type StatsChartHeight = 'sm' | 'md' | 'lg';

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
  @Input() height: StatsChartHeight = 'lg';

  chartData: ChartData<'bar'> = {labels: [], datasets: []};
  chartOptions: ChartConfiguration<'bar'>['options'] = {};

  get heightClass(): string {
    return `zx-stats-bar-chart__canvas zx-stats-bar-chart__canvas--${this.height}`;
  }

  ngOnChanges(): void {
    const data = this.percentage ? this.toPercentages() : this.datasets.map(dataset => dataset.data);
    const tickFontSize = this.cssNumber('--zx-stats-chart-tick-font-size');
    const tickAutoSkipPadding = this.cssNumber('--zx-stats-chart-tick-auto-skip-padding');
    const gridColor = this.cssValue('--zx-stats-chart-grid-color');
    const barRadius = this.cssNumber('--zx-stats-chart-bar-radius');

    this.chartData = {
      labels: this.labels,
      datasets: this.datasets.map((dataset, index) => ({
        label: dataset.label,
        data: data[index],
        backgroundColor: dataset.color,
        hoverBackgroundColor: dataset.color,
        borderWidth: 0,
        borderRadius: barRadius,
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
          ticks: {maxRotation: 0, autoSkipPadding: tickAutoSkipPadding, font: {size: tickFontSize}},
        },
        y: {
          stacked: this.stacked,
          beginAtZero: true,
          max: this.percentage ? 100 : undefined,
          grid: {color: gridColor},
          ticks: {font: {size: tickFontSize}, precision: 0},
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

  private cssValue(name: string): string {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  }

  private cssNumber(name: string): number {
    return Number(this.cssValue(name));
  }
}
