import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const TUNE_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'showPublicForm',
    privilege: 'showPublicForm',
    labelKey: 'tune-details.edit',
  },
  {
    action: 'publicDelete',
    privilege: 'publicDelete',
    labelKey: 'tune-details.delete',
    color: 'danger',
    confirm: {
      titleKey: 'tune-details.delete-confirm-title',
      messageKey: 'tune-details.delete-confirm-message',
      confirmLabelKey: 'tune-details.delete-confirm-yes',
      cancelLabelKey: 'tune-details.delete-confirm-cancel',
    },
  },
];

@Component({
  selector: 'zx-tune-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-tune-editing-controls.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTuneEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) tuneUrl!: string;

  readonly editActions = TUNE_EDIT_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string =>
    `${this.tuneUrl}id:${elementId}/action:${action}/`;
}
