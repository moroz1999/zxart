import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable, switchMap} from 'rxjs';
import {ContentService} from '../../features/content/services/content.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

interface ContentVm {
  titleKey: string;
  html: SafeHtml | null;
}

/**
 * Routed static content page (`/about`, `/about/faq`, `/about/support`,
 * `/about/api`). The page + title come from route data; the HTML is fetched from
 * the backend and rendered in a plain container.
 */
@Component({
  selector: 'zx-content-page',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './content-page.component.html',
  styleUrls: ['./content-page.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ContentPageComponent {
  readonly vm$: Observable<ContentVm> = this.route.data.pipe(
    switchMap(data => this.contentService.getContent(data['page'] as string).pipe(
      map(html => ({
        titleKey: (data['titleKey'] ?? '') as string,
        // The copy is bundled with the backend, not user input; keep it verbatim so
        // the in-page anchors (`id`/`name`, stripped by the sanitizer) keep working.
        html: html === null ? null : this.sanitizer.bypassSecurityTrustHtml(html),
      })),
    )),
  );

  constructor(
    private readonly route: ActivatedRoute,
    private readonly contentService: ContentService,
    private readonly sanitizer: DomSanitizer,
  ) {}
}
