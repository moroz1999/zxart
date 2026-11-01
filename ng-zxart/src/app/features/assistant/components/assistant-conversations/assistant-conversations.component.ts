import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {DatePipe} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {AssistantConversationSummary} from '../../../../shared/models/assistant-data';
import {environment} from '../../../../../environments/environment';

/** The user's conversations: pick one, or delete it. */
@Component({
  selector: 'zx-assistant-conversations',
  standalone: true,
  imports: [
    DatePipe,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxInlineComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    TextDirective,
  ],
  templateUrl: './assistant-conversations.component.html',
  styleUrls: ['./assistant-conversations.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantConversationsComponent {
  @Input({required: true}) conversations: AssistantConversationSummary[] = [];
  @Input() currentId: number | null = null;
  @Output() readonly picked = new EventEmitter<number>();
  @Output() readonly deleted = new EventEmitter<AssistantConversationSummary>();

  constructor(iconReg: SvgIconRegistryService) {
    iconReg.loadSvg(`${environment.svgUrl}delete.svg`, 'delete')?.subscribe();
  }
}
