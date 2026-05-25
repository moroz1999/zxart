import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const AUTHOR_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'showPublicForm',
    privilege: 'publicReceive',
    labelKey: 'author-details.action.showPublicForm',
  },
  {
    action: 'claim',
    privilege: 'claim',
    labelKey: 'author-details.action.claim',
    color: 'secondary',
  },
  {
    action: 'showJoinForm',
    privilege: 'join',
    labelKey: 'author-details.action.showJoinForm',
    color: 'secondary',
  },
  {
    action: 'convertToGroup',
    privilege: 'convertToGroup',
    labelKey: 'author-details.action.convertToGroup',
    color: 'secondary',
  },
  {
    action: 'publicDelete',
    privilege: 'publicDelete',
    labelKey: 'author-details.action.publicDelete',
    color: 'danger',
    confirm: {
      titleKey: 'author-details.action.delete-confirm-title',
      messageKey: 'author-details.action.delete-confirm-message',
      confirmLabelKey: 'author-details.action.delete-confirm-yes',
      cancelLabelKey: 'author-details.action.delete-confirm-cancel',
    },
  },
];

const AUTHOR_ADD_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'authorAlias.showPublicForm',
    privilege: 'authorAlias.showPublicForm',
    labelKey: 'author-details.action.add-alias',
    color: 'secondary',
  },
  {
    action: 'picturesUploadForm.batchUploadForm',
    privilege: 'picturesUploadForm.batchUploadForm',
    labelKey: 'author-details.action.upload-pictures',
    color: 'secondary',
  },
  {
    action: 'musicUploadForm.batchUploadForm',
    privilege: 'musicUploadForm.batchUploadForm',
    labelKey: 'author-details.action.upload-music',
    color: 'secondary',
  },
  {
    action: 'zxProdsUploadForm.batchUploadForm',
    privilege: 'zxProdsUploadForm.batchUploadForm',
    labelKey: 'author-details.action.upload-prods',
    color: 'secondary',
  },
];

@Component({
  selector: 'zx-author-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-author-editing-controls.component.html',
  styleUrl: './zx-author-editing-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) authorUrl!: string;

  readonly editActions = AUTHOR_EDIT_ACTIONS;
  readonly addActions = AUTHOR_ADD_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string => {
    if (action.includes('.')) {
      const dot = action.indexOf('.');
      const type = action.substring(0, dot);
      const act = action.substring(dot + 1);
      return `${this.authorUrl}type:${type}/action:${act}/`;
    }
    return `${this.authorUrl}id:${elementId}/action:${action}/`;
  };
}
