import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {combineLatest, Observable} from 'rxjs';
import {map, startWith} from 'rxjs/operators';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ThemeService} from '../../../settings/services/theme.service';
import {StatsCategoryKey, StatsCategorySection} from '../../models/stats.models';
import {StatsService} from '../../services/stats.service';
import {ZxStatsBarChartComponent, StatsBarDataset} from '../zx-stats-bar-chart/zx-stats-bar-chart.component';
import {ZxStatsLineChartComponent} from '../zx-stats-line-chart/zx-stats-line-chart.component';
import {ZxStatsTopTableComponent} from '../zx-stats-top-table/zx-stats-top-table.component';
import {ZxStatsSectionSkeletonComponent} from '../zx-stats-section-skeleton/zx-stats-section-skeleton.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {LabelDirective} from '../../../../shared/ui/typography/directives/label.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

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
    ZxInlineComponent,
    ZxStatsBarChartComponent,
    ZxStatsLineChartComponent,
    ZxStatsTopTableComponent,
    ZxStatsSectionSkeletonComponent,
    HeadingDirective,
    LabelDirective,
    TextDirective,
  ],
  templateUrl: './zx-stats-category.component.html',
  styleUrl: './zx-stats-category.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsCategoryComponent implements OnInit {
  private static readonly palette = [
    '--zx-stats-chart-series-primary',
    '--zx-stats-chart-series-tertiary',
    '--zx-stats-chart-series-danger',
    '--zx-stats-chart-series-alternate',
    '--zx-stats-chart-series-soft',
    '--zx-stats-chart-series-warm',
    '--zx-stats-chart-series-bold',
    '--zx-stats-chart-series-cool',
    '--zx-stats-chart-series-mellow',
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
    const section$ = this.getSection();
    const labels$ = this.translate.stream(['stats.legend.all', 'stats.legend.rated']);

    this.loaded$ = section$.pipe(map(section => section !== null), startWith(false));
    this.vm$ = combineLatest([section$, this.themeService.theme$, labels$]).pipe(
      map(([section, , labels]) => (section ? this.buildVm(section, labels) : null)),
    );
  }

  private buildVm(section: StatsCategorySection, labels: Record<string, string>): CategoryVm {
    const remainder = section.series.all.map((total, index) => Math.max(0, total - section.series.rated[index]));
    const yearLabels = section.series.years.map(year => String(year));

    const yearDatasets: StatsBarDataset[] = [
      {
        label: labels['stats.legend.rated'],
        data: section.series.rated,
        color: this.color('--zx-stats-chart-series-primary'),
        colorClass: 'zx-stats-category__legend-swatch--primary',
      },
      {
        label: labels['stats.legend.all'],
        data: remainder,
        color: this.color('--zx-stats-chart-series-secondary'),
        colorClass: 'zx-stats-category__legend-swatch--secondary',
      },
    ];

    const distributions = section.distributions.map(distribution => {
      const prefix = this.distributionLabelPrefix(distribution.titleKey);
      const colors = this.distributionColors(distribution.classes.length);

      return {
        title: distribution.titleKey,
        labels: yearLabels,
        datasets: distribution.classes.map((className, index) => ({
          label: this.translateClass(prefix, className),
          data: distribution.rows.map(row => row[index] ?? 0),
          color: colors[index],
        })),
      };
    });

    return {
      section,
      yearLabels,
      yearDatasets,
      avgLabels: yearLabels,
      avgData: section.series.avg,
      avgColor: this.color('--zx-stats-chart-series-primary'),
      avgFill: this.color('--zx-stats-chart-fill-primary'),
      distributions,
      dailyLabels: section.daily.dates,
      dailyDatasets: [
        {label: section.daily.labelKey, data: section.daily.data, color: this.color('--zx-stats-chart-series-daily')},
      ],
    };
  }

  private getSection(): Observable<StatsCategorySection | null> {
    switch (this.category) {
      case 'soft':
        return this.statsService.soft$;
      case 'music':
        return this.statsService.music$;
      case 'gfx':
        return this.statsService.gfx$;
    }
  }

  private color(name: string): string {
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    return value;
  }

  private distributionLabelPrefix(titleKey: string): string | null {
    // Format and hardware codes have ready-made short translations; category titles are already localized server-side.
    switch (titleKey) {
      case 'stats.dist.gfx_type':
        return 'picture-format-short';
      case 'stats.dist.computer_model':
        return 'hardware-short';
      default:
        return null;
    }
  }

  private translateClass(prefix: string | null, className: string): string {
    if (!prefix) {
      return className;
    }
    const key = `${prefix}.${className}`;
    const translated = this.translate.instant(key);

    return translated === key ? className : translated;
  }

  private distributionColors(count: number): string[] {
    const brand = ZxStatsCategoryComponent.palette.map(variable => this.color(variable));
    if (count <= brand.length) {
      return brand.slice(0, count);
    }

    // More classes than the curated palette offers: spread hues evenly so every class stays distinct.
    return Array.from({length: count}, (_, index) => `hsl(${Math.round((360 / count) * index)}, 62%, 52%)`);
  }
}
