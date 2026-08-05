import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {BreakpointObserver} from '@angular/cdk/layout';
import {map, Observable} from 'rxjs';
import {PressMentionDto, PressPublicationDto} from '../../models/press-details.dto';
import {ZxBreakpoints} from '../../../../shared/breakpoints';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxFactComponent} from '../../../../shared/ui/zx-facts/zx-fact.component';
import {ZxFactsComponent} from '../../../../shared/ui/zx-facts/zx-facts.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

/**
 * Companion column of a press article: the issue it belongs to and the issue's
 * table of contents with the current article marked. The table of contents is
 * dropped below the desktop breakpoint, where the column sits above the article
 * and must stay short.
 */
@Component({
  selector: 'zx-press-issue-nav',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    TranslateModule,
    ZxButtonComponent,
    ZxFactComponent,
    ZxFactsComponent,
    ZxPanelComponent,
    ZxPopoverMenuItemComponent,
    ZxStackComponent,
    TextDirective,
  ],
  templateUrl: './zx-press-issue-nav.component.html',
  styleUrls: ['./zx-press-issue-nav.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPressIssueNavComponent {
  @Input({required: true}) publication!: PressPublicationDto;
  @Input({required: true}) currentArticleId!: number;

  readonly isDesktop$: Observable<boolean> = this.breakpointObserver
    .observe(ZxBreakpoints.Desktop)
    .pipe(map(state => state.matches));

  constructor(private readonly breakpointObserver: BreakpointObserver) {}

  trackById(_index: number, article: PressMentionDto): number {
    return article.id;
  }
}
