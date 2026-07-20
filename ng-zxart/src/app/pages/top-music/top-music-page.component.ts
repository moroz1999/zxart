import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxMusicBrowserComponent} from '../../features/music-browser/components/zx-music-browser/zx-music-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

@Component({
  selector: 'zx-top-music-page',
  standalone: true,
  imports: [TranslateModule, ZxMusicBrowserComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './top-music-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TopMusicPageComponent {}
