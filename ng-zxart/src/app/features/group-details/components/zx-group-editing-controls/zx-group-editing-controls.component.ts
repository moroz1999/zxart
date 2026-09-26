import {ChangeDetectionStrategy, Component, inject, Input, OnChanges} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {GroupDataApiService} from '../../../../shared/api/group-data-api.service';
import {GroupAliasDataApiService} from '../../../../shared/api/group-alias-data-api.service';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

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

  private readonly groupDataApi = inject(GroupDataApiService);
  private readonly groupAliasDataApi = inject(GroupAliasDataApiService);

  private readonly groupEditActions: readonly ZxEditingControlAction[] = [
    {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'group-details.action.showPublicForm'},
    {action: 'showJoinForm', privilege: 'join', labelKey: 'group-details.action.showJoinForm', color: 'secondary'},
    {
      action: 'convertToAuthor',
      privilege: 'convertToAuthor',
      labelKey: 'group-details.action.convertToAuthor',
      color: 'secondary',
      confirm: {messageKey: 'convert.group-to-author', confirmLabelKey: 'convert.confirm'},
      run: {execute: id => this.groupDataApi.convertToAuthor(id), targetPath: 'author', failureKey: 'convert.failed'},
    },
  ];

  private readonly groupAliasEditActions: readonly ZxEditingControlAction[] = [
    {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'group-details.action.showPublicForm'},
    {action: 'showJoinForm', privilege: 'join', labelKey: 'group-details.action.showJoinForm', color: 'secondary'},
    {
      action: 'convertToGroup',
      privilege: 'convertToGroup',
      labelKey: 'group-details.action.convertToGroup',
      color: 'secondary',
      confirm: {messageKey: 'convert.alias-to-group', confirmLabelKey: 'convert.confirm'},
      run: {execute: id => this.groupAliasDataApi.convertToGroup(id), targetPath: 'group', failureKey: 'convert.failed'},
    },
  ];

  editActions: readonly ZxEditingControlAction[] = this.groupEditActions;
  readonly addActions = ADD_ACTIONS;

  ngOnChanges(): void {
    this.editActions = this.entityType === 'groupAlias' ? this.groupAliasEditActions : this.groupEditActions;
  }

  readonly buildActionUrl = (action: string, elementId: number): string => {
    const base = this.entityType === 'groupAlias' ? 'group-alias' : 'group';
    if (action === 'showJoinForm') {
      return `/${base}/${elementId}/join`;
    }
    return `/${base}/${elementId}/edit`;
  };

  readonly buildAddActionUrl = (_action: string, elementId: number): string =>
    `/group/${elementId}/prods/add`;
}
