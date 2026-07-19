import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxMusicBrowserComponent} from '../../features/music-browser/components/zx-music-browser/zx-music-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-top-music-page',
  standalone: true,
  imports: [TranslateModule, ZxMusicBrowserComponent, HeadingDirective, ZxStackComponent],
  template: `
    <zx-stack spacing="xl">
      <h1 appHeading="headline">{{ 'top-music.title' | translate }}</h1>
      <zx-music-browser
        [manageUrl]="false"
        fixedSorting="votes,desc"
        [showSorting]="false"
      ></zx-music-browser>
    </zx-stack>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TopMusicPageComponent {}
