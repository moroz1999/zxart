import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxProdCardComponent} from '../../../../shared/ui/zx-prod-card/zx-prod-card.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ProdCompilationsApiService} from '../../services/prod-compilations-api.service';
import {ProdSummaryDto} from '../../models/prod-summary.dto';

@Component({
  selector: 'zx-prod-compilations-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxSkeletonComponent,
    ZxProdCardComponent,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-prod-compilations-section.component.html',
  styleUrls: ['./zx-prod-compilations-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdCompilationsSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  prods: ProdSummaryDto[] = [];

  constructor(
    private readonly api: ProdCompilationsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.api.getProds(this.elementId).subscribe(prods => {
      this.prods = prods;
      this.loaded = true;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  trackById(_index: number, prod: ProdSummaryDto): number {
    return prod.id;
  }
}
