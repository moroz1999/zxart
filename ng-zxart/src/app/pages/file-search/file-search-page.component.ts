import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ParserComponent} from '../../features/parser/parser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed file-search entrypoint (`/file-search`): upload a file, get the releases it belongs to. */
@Component({
  selector: 'zx-file-search-page',
  standalone: true,
  imports: [ParserComponent, TranslateModule, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './file-search-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FileSearchPageComponent {}
