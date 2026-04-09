import {ChangeDetectionStrategy, ChangeDetectorRef, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {environment} from '../../../../../environments/environment';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxProdBlockComponent} from '../../../../shared/ui/zx-prod-block/zx-prod-block.component';
import {ZxProdRowComponent} from '../../../../entities/zx-prod-row/zx-prod-row.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxSortSelectComponent} from '../../../../shared/ui/zx-sort-select/zx-sort-select.component';
import {ZxToggleComponent, ZxToggleOption} from '../../../../shared/ui/zx-toggle/zx-toggle.component';
import {ZxProdsGridDirective} from '../../../../shared/directives/prods-grid.directive';
import {ProdsBrowserService} from '../../services/prods-browser.service';
import {BrowserBaseComponent} from '../../../../shared/browser-base.component';

export type ProdsBrowserLayout = 'loading' | 'screenshots' | 'table';

@Component({
  selector: 'zx-prods-browser',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdBlockComponent,
    ZxProdRowComponent,
    ZxSpinnerComponent,
    ZxCaptionDirective,
    ZxPaginationComponent,
    ZxSortSelectComponent,
    ZxToggleComponent,
    ZxProdsGridDirective,
  ],
  templateUrl: './zx-prods-browser.component.html',
  styleUrls: ['./zx-prods-browser.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdsBrowserComponent extends BrowserBaseComponent {
  prods: ZxProd[] = [];
  layout: ProdsBrowserLayout = 'loading';

  readonly layoutOptions: ZxToggleOption[] = [
    {value: 'loading', icon: 'image'},
    {value: 'screenshots', icon: 'videogame-asset'},
    {value: 'table', icon: 'list'},
  ];

  constructor(
    private prodsBrowserService: ProdsBrowserService,
    private iconReg: SvgIconRegistryService,
    translateService: TranslateService,
    cdr: ChangeDetectorRef,
  ) {
    super(translateService, cdr);
  }

  protected override onBeforeInit(): void {
    for (const name of ['image', 'videogame-asset', 'list']) {
      this.iconReg.loadSvg(`${environment.svgUrl}${name}.svg`, name)?.subscribe();
    }
  }

  onLayoutChange(layout: string): void {
    this.layout = layout as ProdsBrowserLayout;
  }

  protected override fetchPage(start: number, limit: number): void {
    this.prodsBrowserService.getPaged(this.elementId, start, limit, this.sorting).subscribe({
      next: response => {
        this.loading = false;
        this.prods = response.items.map(dto => new ZxProd(dto));
        this.total = response.total;
        this.pagesAmount = Math.ceil(this.total / limit);
        this.cdr.markForCheck();
      },
      error: () => {
        this.loading = false;
        this.error = true;
        this.cdr.markForCheck();
      },
    });
  }
}
