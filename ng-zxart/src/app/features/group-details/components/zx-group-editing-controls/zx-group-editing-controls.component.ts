import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const GROUP_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'group-details.action.showPublicForm'},
  {action: 'showJoinForm', privilege: 'join', labelKey: 'group-details.action.showJoinForm', color: 'secondary'},
  {action: 'convertToAuthor', privilege: 'convertToAuthor', labelKey: 'group-details.action.convertToAuthor', color: 'secondary'},
];

const GROUP_ALIAS_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'group-details.action.showPublicForm'},
  {action: 'showJoinForm', privilege: 'join', labelKey: 'group-details.action.showJoinForm', color: 'secondary'},
  {action: 'convertToGroup', privilege: 'convertToGroup', labelKey: 'group-details.action.convertToGroup', color: 'secondary'},
];

const ADD_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'zxProdsUploadForm.batchUploadForm',
    privilege: 'zxProdsUploadForm.batchUploadForm',
    labelKey: 'group-details.action.upload-prods',
    color: 'secondary',
  },
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
  @Input({required: true}) addActionBaseUrl!: string;

  editActions: readonly ZxEditingControlAction[] = GROUP_EDIT_ACTIONS;
  readonly addActions = ADD_ACTIONS;

  ngOnChanges(): void {
    this.editActions = this.entityType === 'groupAlias' ? GROUP_ALIAS_EDIT_ACTIONS : GROUP_EDIT_ACTIONS;
  }

  readonly buildActionUrl = (action: string, elementId: number): string => {
    const base = this.entityType === 'groupAlias' ? 'group-alias' : 'group';
    switch (action) {
      case 'showJoinForm':
        return `/${base}/${elementId}/join`;
      case 'convertToAuthor':
        return `/group/${elementId}/convert-to-author`;
      case 'convertToGroup':
        return `/group-alias/${elementId}/convert-to-group`;
      default:
        return `/${base}/${elementId}/edit`;
    }
  };

  readonly buildAddActionUrl = (action: string): string => {
    const separatorIndex = action.indexOf('.');
    const type = action.substring(0, separatorIndex);
    const legacyAction = action.substring(separatorIndex + 1);

    return `${this.addActionBaseUrl}type:${type}/action:${legacyAction}/`;
  };
}
