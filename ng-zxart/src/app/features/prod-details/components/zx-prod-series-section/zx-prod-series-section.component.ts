import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxProdsListSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';
import {Observable, of, Subscription} from 'rxjs';
import {ProdRelatedProdsService} from '../../services/prod-related-prods.service';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';

@Component({
  selector: 'zx-prod-series-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdsListSkeletonComponent,
    HeadingDirective,
    ZxProdsListComponent,
    ZxButtonComponent,
    ZxStackComponent,
    ZxInlineComponent,
  ],
  templateUrl: './zx-prod-series-section.component.html',
  styleUrls: ['./zx-prod-series-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdSeriesSectionComponent implements OnInit, OnDestroy {
  @Input({required: true}) elementId!: number;

  prods$: Observable<ZxProd[] | null> = of(null);
  seriesUrl$: Observable<string | null> = of(null);

  private hidden = false;
  private readonly subscription = new Subscription();

  @HostBinding('style.display')
  get display(): string {
    return this.hidden ? 'none' : '';
  }

  constructor(
    private readonly relatedProds: ProdRelatedProdsService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.prods$ = this.relatedProds.getSeries(this.elementId);
    this.seriesUrl$ = this.relatedProds.getSeriesUrl(this.elementId);
    this.subscription.add(
      this.prods$.subscribe(prods => {
        this.hidden = prods !== null && prods.length === 0;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }
}
