import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxPictureSearchComponent
} from '../../features/picture-search/components/zx-picture-search/zx-picture-search.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed graphics-search entrypoint (`/pictures/search`); filters live in the router query params. */
@Component({
  selector: 'zx-picture-search-page',
  standalone: true,
  imports: [TranslateModule, ZxPictureSearchComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './picture-search-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureSearchPageComponent {}
