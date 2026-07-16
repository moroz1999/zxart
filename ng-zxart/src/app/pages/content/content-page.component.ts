import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable, switchMap} from 'rxjs';
import {ContentService} from '../../features/content/services/content.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';

interface ContentVm {
  titleKey: string;
  html: string | null;
}

/**
 * Routed static content page (`/about`, `/about/faq`, `/about/support`,
 * `/about/api`). The page + title come from route data; the HTML is fetched from
 * the backend and rendered in a plain container.
 */
@Component({
  selector: 'zx-content-page',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeadingDirective],
  templateUrl: './content-page.component.html',
  styleUrls: ['./content-page.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ContentPageComponent {
  readonly vm$: Observable<ContentVm> = this.route.data.pipe(
    switchMap(data => this.contentService.getContent(data['page'] as string).pipe(
      map(html => ({titleKey: (data['titleKey'] ?? '') as string, html})),
    )),
  );

  constructor(
    private readonly route: ActivatedRoute,
    private readonly contentService: ContentService,
  ) {}
}
