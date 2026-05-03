import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';
import {Observable, of} from 'rxjs';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ProdRelatedProdsService} from '../../services/prod-related-prods.service';

@Component({
  selector: 'zx-prod-series-prods-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxHeading2Directive,
    ZxProdsListComponent,
  ],
  templateUrl: './zx-prod-series-prods-section.component.html',
  styleUrls: ['./zx-prod-series-prods-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdSeriesProdsSectionComponent implements OnInit {
  @Input({required: true}) elementId!: number;

  prods$: Observable<ZxProd[] | null> = of(null);

  constructor(private readonly relatedProds: ProdRelatedProdsService) {}

  ngOnInit(): void {
    this.prods$ = this.relatedProds.getSeriesProds(this.elementId);
  }
}
