import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxPreformattedComponent} from '../../../../shared/ui/zx-preformatted/zx-preformatted.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {AssistantMessageView, AssistantPlanView} from '../../models/assistant-view';
import {AssistantPlanComponent} from '../assistant-plan/assistant-plan.component';
import {AssistantTextComponent} from '../assistant-text/assistant-text.component';
import {AssistantMarkdownComponent} from '../assistant-markdown/assistant-markdown.component';

/** One message of the conversation, drawn by its kind. */
@Component({
  selector: 'zx-assistant-message',
  standalone: true,
  imports: [
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxPanelComponent,
    ZxPreformattedComponent,
    ZxStackComponent,
    TextDirective,
    AssistantPlanComponent,
    AssistantTextComponent,
    AssistantMarkdownComponent,
  ],
  templateUrl: './assistant-message.component.html',
  styleUrls: ['./assistant-message.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantMessageComponent {
  @Input({required: true}) message!: AssistantMessageView;
  @Output() readonly approve = new EventEmitter<AssistantPlanView>();
  @Output() readonly cancelPlan = new EventEmitter<AssistantPlanView>();

  detailsOpen = false;

  toggleDetails(): void {
    this.detailsOpen = !this.detailsOpen;
  }
}
