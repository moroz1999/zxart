import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  CommentsPageComponent
} from '../../features/comments/components/comments-page/comments-page.component';

/** Routed all-comments entrypoint (`/comments`); the page lives in the router query params. */
@Component({
  selector: 'zx-comments-route-page',
  standalone: true,
  imports: [CommentsPageComponent, TranslateModule],
  template: '<zx-comments-page [manageUrl]="false" [title]="\'menu.comments\' | translate"></zx-comments-page>',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentsRoutePageComponent {}
