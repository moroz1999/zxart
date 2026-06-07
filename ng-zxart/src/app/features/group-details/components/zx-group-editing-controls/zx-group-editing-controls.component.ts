import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const DELETE_CONFIRM = {
  titleKey: 'group-details.action.delete-confirm-title',
  messageKey: 'group-details.action.delete-confirm-message',
  confirmLabelKey: 'group-details.action.delete-confirm-yes',
  cancelLabelKey: 'group-details.action.delete-confirm-cancel',
};

const GROUP_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'group-details.action.showPublicForm'},
  {action: 'showJoinForm', privilege: 'join', labelKey: 'group-details.action.showJoinForm', color: 'secondary'},
  {action: 'convertToAuthor', privilege: 'convertToAuthor', labelKey: 'group-details.action.convertToAuthor', color: 'secondary'},
  {action: 'publicDelete', privilege: 'publicDelete', labelKey: 'group-details.action.publicDelete', color: 'danger', confirm: DELETE_CONFIRM},
];

const GROUP_ALIAS_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'group-details.action.showPublicForm'},
  {action: 'showJoinForm', privilege: 'join', labelKey: 'group-details.action.showJoinForm', color: 'secondary'},
  {action: 'convertToGroup', privilege: 'convertToGroup', labelKey: 'group-details.action.convertToGroup', color: 'secondary'},
  {action: 'publicDelete', privilege: 'publicDelete', labelKey: 'group-details.action.publicDelete', color: 'danger', confirm: DELETE_CONFIRM},
];

const ADD_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'zxProdsUploadForm.batchUploadForm', privilege: 'zxProdsUploadForm.batchUploadForm', labelKey: 'group-details.action.upload-prods', color: 'secondary'},
];

@Component({
  selector: 'zx-group-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-group-editing-controls.component.html',
  styleUrl: './zx-group-editing-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupEditingControlsComponent implements OnChanges {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) entityType!: 'group' | 'groupAlias';
  @Input({required: true}) groupUrl!: string;

  editActions: readonly ZxEditingControlAction[] = GROUP_EDIT_ACTIONS;
  addActions: readonly ZxEditingControlAction[] = ADD_ACTIONS;

  ngOnChanges(): void {
    this.editActions = this.entityType === 'groupAlias' ? GROUP_ALIAS_EDIT_ACTIONS : GROUP_EDIT_ACTIONS;
  }

  readonly buildActionUrl = (action: string, elementId: number): string => {
    if (action.includes('.')) {
      const dot = action.indexOf('.');
      const type = action.substring(0, dot);
      const act = action.substring(dot + 1);
      return `${this.groupUrl}type:${type}/action:${act}/`;
    }
    return `${this.groupUrl}id:${elementId}/action:${action}/`;
  };
}
