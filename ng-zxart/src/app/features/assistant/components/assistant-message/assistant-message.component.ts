import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {AssistantMessageView, AssistantPlanView} from '../../models/assistant-view';
import {AssistantPlanComponent} from '../assistant-plan/assistant-plan.component';
import {AssistantTextComponent} from '../assistant-text/assistant-text.component';

/** One message of the conversation, drawn by its kind. */
@Component({
  selector: 'zx-assistant-message',
  standalone: true,
  imports: [TranslateModule, AssistantPlanComponent, AssistantTextComponent],
  templateUrl: './assistant-message.component.html',
  styleUrls: ['./assistant-message.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantMessageComponent {
  @Input({required: true}) message!: AssistantMessageView;
  @Output() readonly approve = new EventEmitter<AssistantPlanView>();

  detailsOpen = false;

  toggleDetails(): void {
    this.detailsOpen = !this.detailsOpen;
  }
}
