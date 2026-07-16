import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PARTY_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'party-details.action.showPublicForm'},
];

@Component({
  selector: 'zx-party-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-party-editing-controls.component.html',
  styleUrl: './zx-party-editing-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyEditingControlsComponent {
  @Input({required: true}) elementId!: number;

  readonly editActions = PARTY_EDIT_ACTIONS;

  readonly buildActionUrl = (_action: string, elementId: number): string => `/party/${elementId}/edit`;
}
