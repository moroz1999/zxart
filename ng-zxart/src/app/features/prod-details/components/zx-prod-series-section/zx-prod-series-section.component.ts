import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {
  ZxHeading2Directive,
  ZxHeading3Directive,
} from '../../../../shared/directives/typography/typography.directives';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';
import {Observable, of} from 'rxjs';
import {startWith} from 'rxjs/operators';
import {ProdRelatedProdsApiService, ProdSeriesEntry} from '../../services/prod-related-prods-api.service';

@Component({
  selector: 'zx-prod-series-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxSkeletonComponent,
    ZxHeading2Directive,
    ZxHeading3Directive,
    ZxProdsListComponent,
  ],
  templateUrl: './zx-prod-series-section.component.html',
  styleUrls: ['./zx-prod-series-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdSeriesSectionComponent implements OnInit {
  @Input({required: true}) elementId!: number;

  series$: Observable<ProdSeriesEntry[] | null> = of(null);

  constructor(private readonly relatedProdsApi: ProdRelatedProdsApiService) {}

  ngOnInit(): void {
    this.series$ = this.relatedProdsApi.getSeries(this.elementId).pipe(startWith(null));
  }

  trackSeriesById(_index: number, entry: ProdSeriesEntry): number {
    return entry.id;
  }
}
