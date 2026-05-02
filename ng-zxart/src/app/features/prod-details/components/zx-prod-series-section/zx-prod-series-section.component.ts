import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxProdCardComponent} from '../../../../shared/ui/zx-prod-card/zx-prod-card.component';
import {
  ZxHeading2Directive,
  ZxHeading3Directive,
} from '../../../../shared/directives/typography/typography.directives';
import {ProdSeriesApiService} from '../../services/prod-series-api.service';
import {ProdSeriesEntryDto, ProdSummaryDto} from '../../models/prod-summary.dto';

@Component({
  selector: 'zx-prod-series-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxSkeletonComponent,
    ZxProdCardComponent,
    ZxHeading2Directive,
    ZxHeading3Directive,
  ],
  templateUrl: './zx-prod-series-section.component.html',
  styleUrls: ['./zx-prod-series-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdSeriesSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  series: ProdSeriesEntryDto[] = [];

  constructor(
    private readonly api: ProdSeriesApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.api.getSeries(this.elementId).subscribe(series => {
      this.series = series;
      this.loaded = true;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  trackSeriesById(_index: number, entry: ProdSeriesEntryDto): number {
    return entry.id;
  }

  trackProdById(_index: number, prod: ProdSummaryDto): number {
    return prod.id;
  }
}
