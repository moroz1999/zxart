import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ZxAuthorBrowserComponent} from '../../../author-browser/components/zx-author-browser/zx-author-browser.component';
import {ZxActiveAuthorsComponent} from '../zx-active-authors/zx-active-authors.component';

/**
 * Single self-contained authors page (graphics or music): active, top, latest
 * and the full filterable list. The inner lists reuse ZxAuthorBrowserComponent
 * and ZxActiveAuthorsComponent through their Angular-only selectors.
 */
@Component({
  selector: 'zx-authors-page, zx-authors-page-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxStackComponent,
    HeadingDirective,
    ZxAuthorBrowserComponent,
    ZxActiveAuthorsComponent,
  ],
  templateUrl: './zx-authors-page.component.html',
  styleUrls: ['./zx-authors-page.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorsPageComponent {
  @Input() elementId = 0;
  /** Content type of the page: 'graphics' or 'music' (drives sorting, item filter and titles) */
  @Input() items: 'graphics' | 'music' = 'graphics';

  get popularSorting(): string {
    return this.items === 'music' ? 'musicRating,desc' : 'graphicsRating,desc';
  }
}
