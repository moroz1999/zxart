import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {DatePipe} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxInsetComponent} from '../../../../shared/ui/zx-inset/zx-inset.component';
import {ZxListItemComponent} from '../../../../shared/ui/zx-list-item/zx-list-item.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {AssistantConversationSummary} from '../../../../shared/models/assistant-data';
import {environment} from '../../../../../environments/environment';
import {AssistantConversationGroup} from '../../models/assistant-view';
import {groupConversations} from '../../services/assistant-view';

/** The user's conversations by day: pick one, or delete it. */
@Component({
  selector: 'zx-assistant-conversations',
  standalone: true,
  imports: [
    DatePipe,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxInsetComponent,
    ZxListItemComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    TextDirective,
  ],
  templateUrl: './assistant-conversations.component.html',
  styleUrls: ['./assistant-conversations.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantConversationsComponent implements OnChanges {
  @Input({required: true}) conversations: AssistantConversationSummary[] = [];
  @Input() currentId: number | null = null;
  @Output() readonly picked = new EventEmitter<number>();
  @Output() readonly deleted = new EventEmitter<AssistantConversationSummary>();

  groups: AssistantConversationGroup[] = [];

  constructor(iconReg: SvgIconRegistryService) {
    iconReg.loadSvg(`${environment.svgUrl}delete.svg`, 'delete')?.subscribe();
  }

  ngOnChanges(): void {
    this.groups = groupConversations(this.conversations, new Date());
  }
}
