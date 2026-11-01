import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxCalloutComponent} from '../../../../shared/ui/zx-callout/zx-callout.component';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxItemDataComponent} from '../../../../shared/ui/zx-item-data/zx-item-data.component';
import {ZxItemDataItemComponent} from '../../../../shared/ui/zx-item-data/zx-item-data-item.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {TypographyTone} from '../../../../shared/ui/typography/typography.types';
import {AssistantActionStatus} from '../../../../shared/models/assistant-data';
import {AssistantPlanView} from '../../models/assistant-view';
import {AssistantTextComponent} from '../assistant-text/assistant-text.component';

/** A proposed plan: its actions with their previews, and the approve button. */
@Component({
  selector: 'zx-assistant-plan',
  standalone: true,
  imports: [
    TranslateModule,
    ZxButtonComponent,
    ZxCalloutComponent,
    ZxChipComponent,
    ZxInlineComponent,
    ZxItemDataComponent,
    ZxItemDataItemComponent,
    ZxPanelComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    AssistantTextComponent,
  ],
  templateUrl: './assistant-plan.component.html',
  styleUrls: ['./assistant-plan.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantPlanComponent {
  @Input({required: true}) plan!: AssistantPlanView;
  @Output() readonly approve = new EventEmitter<AssistantPlanView>();

  readonly statusTones: Record<AssistantActionStatus, TypographyTone> = {
    pending: 'muted',
    done: 'strong',
    failed: 'danger',
    skipped: 'muted',
  };
}
