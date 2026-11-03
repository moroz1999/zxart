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
import {ZxStepComponent} from '../../../../shared/ui/zx-steps/zx-step.component';
import {ZxStepsComponent} from '../../../../shared/ui/zx-steps/zx-steps.component';
import {ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {AssistantPlanView} from '../../models/assistant-view';
import {isDestructiveKind} from '../../services/assistant-view';
import {AssistantTextComponent} from '../assistant-text/assistant-text.component';

/** A plan: its steps with what each changes and its outcome, and, while it waits, approve and cancel. */
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
    ZxStepComponent,
    ZxStepsComponent,
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
  @Output() readonly cancelPlan = new EventEmitter<AssistantPlanView>();

  get statusColor(): ZxChipColor {
    switch (this.plan.status) {
      case 'proposed':
        return 'primary';
      case 'failed':
        return 'danger';
      default:
        return 'neutral';
    }
  }

  /** Executing and done plans count their steps; a failed one names where it stopped. */
  get statusKey(): string {
    switch (this.plan.status) {
      case 'executing':
      case 'done':
        return 'assistant.plan-status.progress';
      case 'failed':
        return this.plan.stoppedAt !== null ? 'assistant.plan-status.failed-at' : 'assistant.plan-status.failed';
      default:
        return `assistant.plan-status.${this.plan.status}`;
    }
  }

  get statusParams(): Record<string, number> {
    return {done: this.plan.doneCount, total: this.plan.actions.length, step: this.plan.stoppedAt ?? 0};
  }

  readonly isDestructive = isDestructiveKind;
}
