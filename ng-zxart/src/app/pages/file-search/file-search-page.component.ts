import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxFileSearchComponent
} from '../../features/file-search/components/zx-file-search/zx-file-search.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed file-search entrypoint (`/file-search`). */
@Component({
  selector: 'zx-file-search-page',
  standalone: true,
  imports: [ZxFileSearchComponent, TranslateModule, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './file-search-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FileSearchPageComponent {}
