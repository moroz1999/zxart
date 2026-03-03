import {Component, Input} from '@angular/core';
import {ZxItemControlsComponent} from '../zx-item-controls/zx-item-controls.component';

/**
 * Legacy bridge for using zx-item-controls as a custom element in Smarty templates.
 *
 * All inputs are strings because custom elements receive attribute values as strings.
 * This component converts them to the correct types and delegates to zx-item-controls.
 *
 * Registered as the `zx-item-legacy-controls` custom element in AppModule.
 *
 * Usage in Smarty:
 *   <zx-item-legacy-controls
 *     element-id="{$element->id}"
 *     type="zxPicture"
 *     votes="{$element->votes}"
 *     votes-amount="{$element->votesAmount}"
 *     user-vote="{$element->getUserVote()}"
 *     deny-voting="{if $element->isVotingDenied()}true{else}false{/if}"
 *   ></zx-item-legacy-controls>
 */
@Component({
  selector: 'zx-item-legacy-controls',
  standalone: true,
  imports: [ZxItemControlsComponent],
  templateUrl: './zx-item-legacy-controls.component.html',
})
export class ZxItemLegacyControlsComponent {
  @Input() elementId: string = '0';
  @Input() type: string = '';
  @Input() votes: string = '0';
  @Input() votesAmount: string = '0';
  @Input() userVote: string = '';
  @Input() denyVoting: string = 'false';
}
