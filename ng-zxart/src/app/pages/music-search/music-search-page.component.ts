import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxMusicSearchComponent
} from '../../features/music-search/components/zx-music-search/zx-music-search.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed music-search entrypoint (`/music/search`); filters live in the router query params. */
@Component({
  selector: 'zx-music-search-page',
  standalone: true,
  imports: [TranslateModule, ZxMusicSearchComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './music-search-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MusicSearchPageComponent {}
