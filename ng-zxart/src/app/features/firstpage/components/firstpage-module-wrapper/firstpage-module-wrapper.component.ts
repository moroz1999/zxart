import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective, ZxHeading3Directive} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'app-firstpage-module-wrapper',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxSkeletonComponent,
    ZxHeading3Directive,
    ZxCaptionDirective,
  ],
  templateUrl: './firstpage-module-wrapper.component.html',
  styleUrls: ['./firstpage-module-wrapper.component.scss']
})
export class FirstpageModuleWrapperComponent {
  @Input() titleKey!: string;
  @Input() viewAllUrl?: string;
  @Input() loading = false;
  @Input() error = false;
  @Input() empty = false;
  @Input() usePanel = true;
  @Input() skeletonVariant: 'card' | 'row' | 'prod-grid' | 'picture-grid' = 'card';
  @Input() skeletonCount = 4;
}
