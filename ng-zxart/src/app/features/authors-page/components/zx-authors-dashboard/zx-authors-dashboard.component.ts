import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxAuthorBrowserComponent} from '../../../author-browser/components/zx-author-browser/zx-author-browser.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ZxNavChipsComponent} from '../../../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {buildLetterChips, ZxNavChip} from '../../../../shared/ui/zx-nav-chips/nav-chip';
import {ZxActiveAuthorsComponent} from '../zx-active-authors/zx-active-authors.component';

@Component({
  selector: 'zx-authors-dashboard',
  standalone: true,
  imports: [TranslateModule, ZxAuthorBrowserComponent, ZxStackComponent, HeadingDirective, ZxNavChipsComponent, ZxActiveAuthorsComponent],
  templateUrl: './zx-authors-dashboard.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorsDashboardComponent {
  @Input({required: true}) items: 'graphics' | 'music' = 'graphics';
  @Input({required: true}) basePath = '/artists';

  get popularSorting(): string {
    return this.items === 'music' ? 'musicRating,desc' : 'graphicsRating,desc';
  }

  get letterChips(): ZxNavChip[] {
    return buildLetterChips(this.basePath, '');
  }
}
