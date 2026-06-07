import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {GroupProdEntry, GroupProdsApiService} from '../../services/group-prods-api.service';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxProdBlockComponent} from '../../../../entities/zx-prod-block/zx-prod-block.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxProdsGridDirective} from '../../../../shared/directives/prods-grid.directive';

const BEST_LIMIT = 5;

@Component({
  selector: 'zx-group-best-works',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdBlockComponent,
    ZxPanelComponent,
    ZxProdsListSkeletonComponent,
    ZxButtonComponent,
    ZxProdsGridDirective,
  ],
  templateUrl: './zx-group-best-works.component.html',
  styleUrl: './zx-group-best-works.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupBestWorksComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  @Input() viewAllUrl = '';

  loading = true;
  prods: ZxProd[] = [];

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly prodsApiService: GroupProdsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(
      this.prodsApiService.getProds(this.elementId, 'own', 0, BEST_LIMIT, 'votes', 'desc', '', 0).subscribe(result => {
        this.loading = false;
        this.prods = result.items
          .filter((item): item is GroupProdEntry => item.type === 'prod')
          .map(item => new ZxProd(item));
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }
}
