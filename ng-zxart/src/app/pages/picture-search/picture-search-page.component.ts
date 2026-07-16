import {ChangeDetectionStrategy, Component} from '@angular/core';
import {
  ZxPictureSearchComponent
} from '../../features/picture-search/components/zx-picture-search/zx-picture-search.component';

/** Routed graphics-search entrypoint (`/pictures/search`); filters live in the router query params. */
@Component({
  selector: 'zx-picture-search-page',
  standalone: true,
  imports: [ZxPictureSearchComponent],
  template: '<zx-picture-search [manageUrl]="false"></zx-picture-search>',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureSearchPageComponent {}
