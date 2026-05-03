import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxProdsListSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxHeading2Directive,} from '../../../../shared/directives/typography/typography.directives';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';
import {Observable, of} from 'rxjs';
import {ProdRelatedProdsService} from '../../services/prod-related-prods.service';
import {ZxProd} from '../../../../shared/models/zx-prod';

@Component({
  selector: 'zx-prod-series-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdsListSkeletonComponent,
    ZxHeading2Directive,
    ZxProdsListComponent,
  ],
  templateUrl: './zx-prod-series-section.component.html',
  styleUrls: ['./zx-prod-series-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdSeriesSectionComponent implements OnInit {
  @Input({required: true}) elementId!: number;

  prods$: Observable<ZxProd[] | null> = of(null);

  constructor(private readonly relatedProds: ProdRelatedProdsService) {}

  ngOnInit(): void {
    this.prods$ = this.relatedProds.getSeries(this.elementId);
  }
}
