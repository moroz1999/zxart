import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {TuneDetailsDto} from '../../models/tune-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxItemDataItemComponent} from '../../../../shared/ui/zx-item-data/zx-item-data-item.component';
import {ZxCollapsibleSectionComponent} from '../../../../shared/ui/zx-collapsible-section/zx-collapsible-section.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

@Component({
  selector: 'zx-tune-meta-panel',
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
  templateUrl: './zx-tune-meta-panel.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTuneMetaPanelComponent {
  @Input({required: true}) tune!: TuneDetailsDto;

  get hasTechInfo(): boolean {
    return !!(
      this.tune.frequency ||
      this.tune.intFrequency ||
      this.tune.fileName ||
      this.tune.converterVersion
    );
  }
}
