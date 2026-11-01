import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxCalloutComponent} from '../../../../shared/ui/zx-callout/zx-callout.component';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {AssistantPlanView} from '../../models/assistant-view';
import {AssistantTextComponent} from '../assistant-text/assistant-text.component';

/** A proposed plan: its actions with their previews, and the approve button. */
@Component({
  selector: 'zx-assistant-plan',
  standalone: true,
  imports: [TranslateModule, ZxButtonComponent, ZxCalloutComponent, ZxChipComponent, AssistantTextComponent],
  templateUrl: './assistant-plan.component.html',
  styleUrls: ['./assistant-plan.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantPlanComponent {
  @Input({required: true}) plan!: AssistantPlanView;
  @Output() readonly approve = new EventEmitter<AssistantPlanView>();
}
