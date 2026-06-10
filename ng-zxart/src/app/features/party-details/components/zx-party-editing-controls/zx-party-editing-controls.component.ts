import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const DELETE_CONFIRM = {
  titleKey: 'party-details.action.delete-confirm-title',
  messageKey: 'party-details.action.delete-confirm-message',
  confirmLabelKey: 'party-details.action.delete-confirm-yes',
  cancelLabelKey: 'party-details.action.delete-confirm-cancel',
};

const PARTY_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'party-details.action.showPublicForm'},
  {action: 'publicDelete', privilege: 'publicDelete', labelKey: 'party-details.action.publicDelete', color: 'danger', confirm: DELETE_CONFIRM},
];

const PARTY_ADD_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'picturesUploadForm.batchUploadForm', privilege: 'picturesUploadForm.batchUploadForm', labelKey: 'party-details.action.upload-pictures', color: 'secondary'},
  {action: 'musicUploadForm.batchUploadForm', privilege: 'musicUploadForm.batchUploadForm', labelKey: 'party-details.action.upload-music', color: 'secondary'},
  {action: 'zxProdsUploadForm.batchUploadForm', privilege: 'zxProdsUploadForm.batchUploadForm', labelKey: 'party-details.action.upload-prods', color: 'secondary'},
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
  @Input({required: true}) partyUrl!: string;

  readonly editActions = PARTY_EDIT_ACTIONS;
  readonly addActions = PARTY_ADD_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string => {
    if (action.includes('.')) {
      const dot = action.indexOf('.');
      const type = action.substring(0, dot);
      const act = action.substring(dot + 1);
      return `${this.partyUrl}type:${type}/action:${act}/`;
    }
    return `${this.partyUrl}id:${elementId}/action:${action}/`;
  };
}
