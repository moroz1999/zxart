import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  CommentsPageComponent
} from '../../features/comments/components/comments-page/comments-page.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed all-comments entrypoint (`/comments`); the page lives in the router query params. */
@Component({
  selector: 'zx-comments-route-page',
  standalone: true,
  imports: [CommentsPageComponent, TranslateModule, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './comments-route-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentsRoutePageComponent {}
