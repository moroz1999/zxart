import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPictureBrowserComponent} from '../../features/picture-browser/components/zx-picture-browser/zx-picture-browser.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

@Component({
  selector: 'zx-top-pictures-page',
  standalone: true,
  imports: [TranslateModule, ZxPictureBrowserComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './top-pictures-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TopPicturesPageComponent {}
