import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PROD_EDITING_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'showPublicForm',
    privilege: 'showPublicForm',
    labelKey: 'prod-details.edit',
  },
  {
    action: 'showAiForm',
    privilege: 'showAiForm',
    labelKey: 'prod-details.showAiForm',
  },
  {
    action: 'resize',
    privilege: 'resize',
    labelKey: 'prod-details.resize',
  },
  {
    action: 'showJoinForm',
    privilege: 'showJoinForm',
    labelKey: 'prod-details.join',
  },
  {
    action: 'showSplitForm',
    privilege: 'showSplitForm',
    labelKey: 'prod-details.split',
  },
  {
    action: 'publicDelete',
    privilege: 'publicDelete',
    labelKey: 'prod-details.delete',
    color: 'danger',
    confirm: {
      titleKey: 'prod-details.delete-confirm-title',
      messageKey: 'prod-details.delete-confirm-message',
      confirmLabelKey: 'prod-details.delete-confirm-yes',
      cancelLabelKey: 'prod-details.delete-confirm-cancel',
    },
  },
];

@Component({
  selector: 'zx-prod-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent],
  templateUrl: './zx-prod-editing-controls.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) prodUrl!: string;

  readonly actions = PROD_EDITING_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string => `${this.prodUrl}id:${elementId}/action:${action}/`;
}
