import {CommonModule} from '@angular/common';
import {
  AfterViewInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  Input,
  OnDestroy,
  OnInit,
  QueryList,
  ViewChild,
  ViewChildren,
} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Observable} from 'rxjs';
import {filter, map, shareReplay, switchMap, take} from 'rxjs/operators';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ThemeService} from '../../../settings/services/theme.service';
import {
  StatsCategoryKey,
  StatsCategorySummary,
  StatsDailySeries,
  StatsDistribution,
  StatsTopUsersSection,
  StatsYearSeries,
} from '../../models/stats.models';
import {StatsService} from '../../services/stats.service';
import {ZxStatsBarChartComponent, StatsBarDataset} from '../zx-stats-bar-chart/zx-stats-bar-chart.component';
import {ZxStatsTopTableComponent} from '../zx-stats-top-table/zx-stats-top-table.component';
import {ZxStatsSectionSkeletonComponent} from '../zx-stats-section-skeleton/zx-stats-section-skeleton.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {LabelDirective} from '../../../../shared/ui/typography/directives/label.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {StatsNumberPipe} from '../../pipes/stats-number.pipe';

interface DistributionVm {
  title: string;
  labels: string[];
  datasets: StatsBarDataset[];
}

interface DistributionBlock {
  action: string;
  visible$: BehaviorSubject<boolean>;
  vm$: Observable<DistributionVm | null>;
}

interface YearVm {
  labels: string[];
  datasets: StatsBarDataset[];
}

interface DailyVm {
  title: string;
  labels: string[];
  datasets: StatsBarDataset[];
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
    ZxStatsTopTableComponent,
    ZxStatsSectionSkeletonComponent,
    HeadingDirective,
    LabelDirective,
    TextDirective,
    StatsNumberPipe,
  ],
  templateUrl: './zx-stats-category.component.html',
  styleUrl: './zx-stats-category.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsCategoryComponent implements OnInit, AfterViewInit, OnDestroy {
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

  @ViewChild('summaryBlock', {static: true}) private summaryBlock!: ElementRef<HTMLElement>;
  @ViewChild('seriesBlock', {static: true}) private seriesBlock!: ElementRef<HTMLElement>;
  @ViewChild('dailyBlock', {static: true}) private dailyBlock!: ElementRef<HTMLElement>;
  @ViewChild('topBlock', {static: true}) private topBlock!: ElementRef<HTMLElement>;
  @ViewChildren('distributionBlock') private distributionBlockElements!: QueryList<ElementRef<HTMLElement>>;

  summary$!: Observable<StatsCategorySummary | null>;
  yearVm$!: Observable<YearVm | null>;
  dailyVm$!: Observable<DailyVm | null>;
  top$!: Observable<StatsTopUsersSection | null>;
  distributionBlocks: DistributionBlock[] = [];

  private readonly summaryVisible$ = new BehaviorSubject(false);
  private readonly seriesVisible$ = new BehaviorSubject(false);
  private readonly dailyVisible$ = new BehaviorSubject(false);
  private readonly topVisible$ = new BehaviorSubject(false);
  private readonly intersectionObservers: IntersectionObserver[] = [];

  constructor(
    private readonly statsService: StatsService,
    private readonly themeService: ThemeService,
    private readonly translate: TranslateService,
    private readonly changeDetector: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    const labels$ = this.translate.stream(['stats.legend.all', 'stats.legend.rated']);

    this.distributionBlocks = this.createDistributionBlocks();
    this.summary$ = this.visibleOnce(this.summaryVisible$).pipe(
      switchMap(() => this.statsService.categorySummary(this.category)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.yearVm$ = this.visibleOnce(this.seriesVisible$).pipe(
      switchMap(() => combineLatest([this.statsService.categorySeries(this.category), this.themeService.theme$, labels$])),
      map(([series, , labels]) => (series ? this.buildYearVm(series, labels) : null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.dailyVm$ = this.visibleOnce(this.dailyVisible$).pipe(
      switchMap(() => combineLatest([this.statsService.categoryDaily(this.category), this.themeService.theme$])),
      map(([daily]) => (daily ? this.buildDailyVm(daily) : null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.top$ = this.visibleOnce(this.topVisible$).pipe(
      switchMap(() => this.statsService.categoryTop(this.category)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  ngAfterViewInit(): void {
    this.observeBlock(this.summaryBlock, this.summaryVisible$);
    this.observeBlock(this.seriesBlock, this.seriesVisible$);
    this.distributionBlockElements.forEach((elementRef, index) => {
      const block = this.distributionBlocks[index];
      if (block) {
        this.observeBlock(elementRef, block.visible$);
      }
    });
    this.observeBlock(this.dailyBlock, this.dailyVisible$);
    this.observeBlock(this.topBlock, this.topVisible$);
  }

  ngOnDestroy(): void {
    this.intersectionObservers.forEach(observer => observer.disconnect());
  }

  private buildYearVm(series: StatsYearSeries, labels: Record<string, string>): YearVm {
    const remainder = series.all.map((total, index) => Math.max(0, total - series.rated[index]));

    return {
      labels: series.years.map(year => String(year)),
      datasets: [
        {
          label: labels['stats.legend.rated'],
          data: series.rated,
          color: this.color('--zx-stats-chart-series-primary'),
          colorClass: 'zx-stats-category__legend-swatch--primary',
        },
        {
          label: labels['stats.legend.all'],
          data: remainder,
          color: this.color('--zx-stats-chart-series-secondary'),
          colorClass: 'zx-stats-category__legend-swatch--secondary',
        },
      ],
    };
  }

  private buildDistributionVm(years: number[], distribution: StatsDistribution): DistributionVm {
    const labels = years.map(year => String(year));
    const prefix = this.distributionLabelPrefix(distribution.titleKey);
    const colors = this.distributionColors(distribution.classes.length);

    return {
      title: distribution.titleKey,
      labels,
      datasets: distribution.classes.map((className, index) => ({
        label: this.translateClass(prefix, className),
        data: distribution.rows.map(row => row[index] ?? 0),
        color: colors[index],
      })),
    };
  }

  private buildDailyVm(daily: StatsDailySeries): DailyVm {
    return {
      title: daily.labelKey,
      labels: daily.dates,
      datasets: [
        {label: daily.labelKey, data: daily.data, color: this.color('--zx-stats-chart-series-daily')},
      ],
    };
  }

  private visibleOnce(source$: Observable<boolean>): Observable<boolean> {
    return source$.pipe(
      filter(Boolean),
      take(1),
    );
  }

  private observeBlock(elementRef: ElementRef<HTMLElement>, visible$: BehaviorSubject<boolean>): void {
    const observer = new IntersectionObserver(entries => {
      if (!entries.some(entry => entry.isIntersecting)) {
        return;
      }

      visible$.next(true);
      observer.disconnect();
      this.changeDetector.markForCheck();
    }, {rootMargin: '0px'});
    observer.observe(elementRef.nativeElement);
    this.intersectionObservers.push(observer);
  }

  private createDistributionBlocks(): DistributionBlock[] {
    return this.distributionActions().map(action => {
      const visible$ = new BehaviorSubject(false);
      const vm$ = this.visibleOnce(visible$).pipe(
        switchMap(() => combineLatest([this.statsService.distributionBlock(action), this.themeService.theme$])),
        map(([block]) => (block ? this.buildDistributionVm(block.years, block.distribution) : null)),
        shareReplay({bufferSize: 1, refCount: false}),
      );

      return {action, visible$, vm$};
    });
  }

  private distributionActions(): string[] {
    switch (this.category) {
      case 'soft':
        return ['soft-category-distribution', 'soft-computer-distribution', 'soft-country-distribution'];
      case 'music':
        return ['music-format-distribution', 'music-country-distribution'];
      case 'gfx':
        return ['gfx-type-distribution', 'gfx-country-distribution'];
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

    return Array.from({length: count}, (_, index) => `hsl(${Math.round((360 / count) * index)}, 62%, 52%)`);
  }
}
