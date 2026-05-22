import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';

@Component({
  selector: 'zx-firstpage-module-wrapper',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    HeadingDirective,
    TextDirective,
    ZxButtonComponent,
  ],
  templateUrl: './firstpage-module-wrapper.component.html',
  styleUrls: ['./firstpage-module-wrapper.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FirstpageModuleWrapperComponent {
  @Input() titleKey!: string;
  @Input() viewAllUrl?: string;
  @Input() viewAllLabelKey?: string;
  @Input() loading = false;
  @Input() error = false;
  @Input() empty = false;
  @Input() usePanel = true;
}
