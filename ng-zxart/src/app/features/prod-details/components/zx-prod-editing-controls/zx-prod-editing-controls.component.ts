import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PROD_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'showPublicForm',
    privilege: 'showPublicForm',
    labelKey: 'prod-details.edit',
  },
  {
    action: 'showAiForm',
    privilege: 'showAiForm',
    labelKey: 'prod-details.showAiForm',
    color: 'secondary',
  },
  {
    action: 'resize',
    privilege: 'resize',
    labelKey: 'prod-details.resize',
    color: 'secondary',
  },
  {
    action: 'showJoinForm',
    privilege: 'showJoinForm',
    labelKey: 'prod-details.join',
    color: 'secondary',
  },
  {
    action: 'showSplitForm',
    privilege: 'showSplitForm',
    labelKey: 'prod-details.split',
    color: 'secondary',
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

const PROD_ADD_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'zxRelease.publicAdd',
    privilege: 'zxRelease.publicAdd',
    labelKey: 'prod-details.addrelease',
    color: 'secondary',
  },
];

@Component({
  selector: 'zx-prod-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-prod-editing-controls.component.html',
  styleUrl: './zx-prod-editing-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) prodUrl!: string;

  readonly editActions = PROD_EDIT_ACTIONS;
  readonly addActions = PROD_ADD_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string => {
    if (action.includes('.')) {
      const dot = action.indexOf('.');
      const type = action.substring(0, dot);
      const act = action.substring(dot + 1);
      return `${this.prodUrl}type:${type}/action:${act}/`;
    }
    return `${this.prodUrl}id:${elementId}/action:${action}/`;
  };
}
