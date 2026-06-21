import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, Observable} from 'rxjs';
import {map, startWith} from 'rxjs/operators';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ThemeService} from '../../../settings/services/theme.service';
import {StatsCategoryKey, StatsCategorySection} from '../../models/stats.models';
import {StatsService} from '../../services/stats.service';
import {ZxStatsBarChartComponent, StatsBarDataset} from '../zx-stats-bar-chart/zx-stats-bar-chart.component';
import {ZxStatsLineChartComponent} from '../zx-stats-line-chart/zx-stats-line-chart.component';
import {ZxStatsTopTableComponent} from '../zx-stats-top-table/zx-stats-top-table.component';
import {ZxStatsSectionSkeletonComponent} from '../zx-stats-section-skeleton/zx-stats-section-skeleton.component';

interface DistributionVm {
  title: string;
  labels: string[];
  datasets: StatsBarDataset[];
}

interface CategoryVm {
  section: StatsCategorySection;
  yearLabels: string[];
  yearDatasets: StatsBarDataset[];
  avgLabels: string[];
  avgData: number[];
  avgColor: string;
  avgFill: string;
  distributions: DistributionVm[];
  dailyLabels: string[];
  dailyDatasets: StatsBarDataset[];
}

@Component({
  selector: 'zx-stats-category',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxGridComponent,
    ZxStackComponent,
    ZxStatsBarChartComponent,
    ZxStatsLineChartComponent,
    ZxStatsTopTableComponent,
    ZxStatsSectionSkeletonComponent,
  ],
  templateUrl: './zx-stats-category.component.html',
  styleUrl: './zx-stats-category.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsCategoryComponent implements OnInit {
  private static readonly palette = [
    '--primary-600',
    '--warning-500',
    '--danger-500',
    '--secondary-500',
    '--primary-300',
    '--warning-700',
  ];

  @Input({required: true}) category!: StatsCategoryKey;

  vm$!: Observable<CategoryVm | null>;
  loaded$!: Observable<boolean>;

  constructor(
    private readonly statsService: StatsService,
    private readonly themeService: ThemeService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    const section$ = this.statsService.getCategory(this.category);
    const labels$ = this.translate.stream(['stats.legend.all', 'stats.legend.rated', 'stats.dist.other']);

    this.loaded$ = section$.pipe(map(section => section !== null), startWith(false));
    this.vm$ = combineLatest([section$, this.themeService.theme$, labels$]).pipe(
      map(([section, , labels]) => (section ? this.buildVm(section, labels) : null)),
    );
  }

  private buildVm(section: StatsCategorySection, labels: Record<string, string>): CategoryVm {
    const remainder = section.series.all.map((total, index) => Math.max(0, total - section.series.rated[index]));
    const yearLabels = section.series.years.map(year => String(year));

    const yearDatasets: StatsBarDataset[] = [
      {label: labels['stats.legend.rated'], data: section.series.rated, color: this.color('--primary-600')},
      {label: labels['stats.legend.all'], data: remainder, color: this.color('--primary-200')},
    ];

    const distributions = section.distributions.map(distribution => ({
      title: distribution.titleKey,
      labels: yearLabels,
      datasets: distribution.classes.map((className, index) => ({
        label: className === 'other' ? labels['stats.dist.other'] : className,
        data: distribution.rows.map(row => row[index] ?? 0),
        color:
          className === 'other'
            ? this.color('--secondary-300')
            : this.color(ZxStatsCategoryComponent.palette[index % ZxStatsCategoryComponent.palette.length]),
      })),
    }));

    return {
      section,
      yearLabels,
      yearDatasets,
      avgLabels: yearLabels,
      avgData: section.series.avg,
      avgColor: this.color('--primary-600'),
      avgFill: this.color('--primary-100'),
      distributions,
      dailyLabels: section.daily.dates,
      dailyDatasets: [
        {label: section.daily.labelKey, data: section.daily.data, color: this.color('--primary-500')},
      ],
    };
  }

  private color(name: string): string {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    return value || '#888';
  }
}
