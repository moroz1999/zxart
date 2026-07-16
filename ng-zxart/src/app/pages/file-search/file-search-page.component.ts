import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxFileSearchComponent
} from '../../features/file-search/components/zx-file-search/zx-file-search.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';

/** Routed file-search entrypoint (`/file-search`). */
@Component({
  selector: 'zx-file-search-page',
  standalone: true,
  imports: [ZxFileSearchComponent, TranslateModule, HeadingDirective],
  template: `
    <h1 appHeading="headline" class="file-search-page__title">{{ 'menu.about-sub.filesearch' | translate }}</h1>
    <zx-file-search></zx-file-search>
  `,
  styles: ['.file-search-page__title { max-width: 720px; margin: var(--space-24) auto 0; padding: 0 var(--space-16); }'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FileSearchPageComponent {}
