import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxCaptionDirective, ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';

@Component({
  selector: 'zx-firstpage-module-wrapper',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxHeading2Directive,
    ZxCaptionDirective,
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
