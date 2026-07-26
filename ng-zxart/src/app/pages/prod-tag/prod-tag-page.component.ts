import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxProdsBrowserComponent} from '../../features/prods-browser/components/zx-prods-browser/zx-prods-browser.component';
import {
  TAG_PAGE_SORTING_KEYS,
  TagPageStateService,
} from '../../features/tag-page/services/tag-page-state.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

@Component({
  selector: 'zx-prod-tag-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdsBrowserComponent,
    HeadingDirective,
    TextDirective,
    ZxPageLayoutComponent,
  ],
  providers: [TagPageStateService],
  templateUrl: './prod-tag-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ProdTagPageComponent {
  readonly vm$ = this.state.vm$;
  readonly sortingKeys = TAG_PAGE_SORTING_KEYS;

  constructor(private readonly state: TagPageStateService) {}
}
