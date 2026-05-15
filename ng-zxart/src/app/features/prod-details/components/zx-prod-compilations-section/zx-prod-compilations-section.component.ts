import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {HeadingDirective, TextDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {Observable, of, Subscription} from 'rxjs';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ProdRelatedProdsService} from '../../services/prod-related-prods.service';

@Component({
  selector: 'zx-prod-compilations-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    HeadingDirective,
    ZxProdsListComponent,
    ZxStackComponent,
  ],
  templateUrl: './zx-prod-compilations-section.component.html',
  styleUrls: ['./zx-prod-compilations-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdCompilationsSectionComponent implements OnInit, OnDestroy {
  @Input({required: true}) elementId!: number;

  prods$: Observable<ZxProd[] | null> = of(null);

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
    this.prods$ = this.relatedProds.getCompilations(this.elementId);
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
