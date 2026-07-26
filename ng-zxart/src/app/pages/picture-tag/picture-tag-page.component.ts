import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPictureBrowserComponent} from '../../features/picture-browser/components/zx-picture-browser/zx-picture-browser.component';
import {
  TAG_PAGE_SORTING_KEYS,
  TagPageStateService,
} from '../../features/tag-page/services/tag-page-state.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

@Component({
  selector: 'zx-picture-tag-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureBrowserComponent,
    HeadingDirective,
    TextDirective,
    ZxPageLayoutComponent,
  ],
  providers: [TagPageStateService],
  templateUrl: './picture-tag-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureTagPageComponent {
  readonly vm$ = this.state.vm$;
  readonly sortingKeys = TAG_PAGE_SORTING_KEYS;

  constructor(private readonly state: TagPageStateService) {}
}
