import {ChangeDetectionStrategy, Component, HostListener, Input} from '@angular/core';
import {Router} from '@angular/router';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

/**
 * The assistant's prose rendered from Markdown. Angular sanitizes the HTML as it
 * binds it. A link to a page of the site goes through the router, so the
 * drawer stays open; other links are left to the browser.
 */
@Component({
  selector: 'zx-assistant-markdown',
  standalone: true,
  imports: [TextDirective],
  templateUrl: './assistant-markdown.component.html',
  styleUrls: ['./assistant-markdown.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantMarkdownComponent {
  @Input({required: true}) html = '';

  constructor(private readonly router: Router) {}

  @HostListener('click', ['$event'])
  onClick(event: MouseEvent): void {
    if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
      return;
    }
    const anchor = (event.target as HTMLElement | null)?.closest('a');
    const href = anchor?.getAttribute('href');
    if (href && href.startsWith('/') && !href.startsWith('//')) {
      event.preventDefault();
      void this.router.navigateByUrl(href);
    }
  }
}
