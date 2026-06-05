import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {PictureDetailsDto} from '../../models/picture-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxItemDataItemComponent} from '../../../../shared/ui/zx-item-data/zx-item-data-item.component';
import {ZxCollapsibleSectionComponent} from '../../../../shared/ui/zx-collapsible-section/zx-collapsible-section.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

@Component({
  selector: 'zx-picture-meta-panel',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxItemDataItemComponent,
    ZxCollapsibleSectionComponent,
    ZxStackComponent,
    TextDirective,
  ],
  templateUrl: './zx-picture-meta-panel.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureMetaPanelComponent {
  @Input({required: true}) picture!: PictureDetailsDto;
}
