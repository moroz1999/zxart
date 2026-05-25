import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const RELEASE_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'showPublicForm',
    privilege: 'showPublicForm',
    labelKey: 'release-details.edit',
  },
  {
    action: 'clone',
    privilege: 'clone',
    labelKey: 'release-details.clone',
    color: 'secondary',
  },
  {
    action: 'publicDelete',
    privilege: 'publicDelete',
    labelKey: 'release-details.delete',
    color: 'danger',
    confirm: {
      titleKey: 'release-details.delete-confirm-title',
      messageKey: 'release-details.delete-confirm-message',
      confirmLabelKey: 'release-details.delete-confirm-yes',
      cancelLabelKey: 'release-details.delete-confirm-cancel',
    },
  },
];

const RELEASE_ADD_ACTIONS: readonly ZxEditingControlAction[] = [];

@Component({
  selector: 'zx-release-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-release-editing-controls.component.html',
  styleUrl: './zx-release-editing-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) releaseUrl!: string;

  readonly editActions = RELEASE_EDIT_ACTIONS;
  readonly addActions = RELEASE_ADD_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string =>
    `${this.releaseUrl}id:${elementId}/action:${action}/`;
}
