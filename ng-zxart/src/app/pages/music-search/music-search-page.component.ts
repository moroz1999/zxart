import {ChangeDetectionStrategy, Component} from '@angular/core';
import {
  ZxMusicSearchComponent
} from '../../features/music-search/components/zx-music-search/zx-music-search.component';

/** Routed music-search entrypoint (`/music/search`); filters live in the router query params. */
@Component({
  selector: 'zx-music-search-page',
  standalone: true,
  imports: [ZxMusicSearchComponent],
  template: '<zx-music-search [manageUrl]="false"></zx-music-search>',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MusicSearchPageComponent {}
