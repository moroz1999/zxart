import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPictureBrowserComponent} from '../../features/picture-browser/components/zx-picture-browser/zx-picture-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-top-pictures-page',
  standalone: true,
  imports: [TranslateModule, ZxPictureBrowserComponent, HeadingDirective, ZxStackComponent],
  template: `
    <zx-stack spacing="xl">
      <h1 appHeading="headline">{{ 'top-pictures.title' | translate }}</h1>
      <zx-picture-browser
        [manageUrl]="false"
        fixedSorting="votes,desc"
        [showSorting]="false"
      ></zx-picture-browser>
    </zx-stack>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TopPicturesPageComponent {}
