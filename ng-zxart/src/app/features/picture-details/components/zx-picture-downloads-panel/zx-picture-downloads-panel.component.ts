import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {PictureDownloadDto} from '../../models/picture-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

@Component({
  selector: 'zx-picture-downloads-panel',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxBadgeComponent,
    ZxStackComponent,
    ZxInlineComponent,
    TextDirective,
  ],
  templateUrl: './zx-picture-downloads-panel.component.html',
  styleUrls: ['./zx-picture-downloads-panel.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureDownloadsPanelComponent {
  @Input({required: true}) downloads: PictureDownloadDto[] = [];
}
