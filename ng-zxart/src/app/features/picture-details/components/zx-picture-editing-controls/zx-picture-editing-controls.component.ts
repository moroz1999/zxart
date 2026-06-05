import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PICTURE_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'showPublicForm',
    privilege: 'showPublicForm',
    labelKey: 'picture-details.edit',
  },
  {
    action: 'publicDelete',
    privilege: 'publicDelete',
    labelKey: 'picture-details.delete',
    color: 'danger',
    confirm: {
      titleKey: 'picture-details.delete-confirm-title',
      messageKey: 'picture-details.delete-confirm-message',
      confirmLabelKey: 'picture-details.delete-confirm-yes',
      cancelLabelKey: 'picture-details.delete-confirm-cancel',
    },
  },
];

@Component({
  selector: 'zx-picture-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-picture-editing-controls.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) pictureUrl!: string;

  readonly editActions = PICTURE_EDIT_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string =>
    `${this.pictureUrl}id:${elementId}/action:${action}/`;
}
