import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const RELEASE_EDITING_ACTIONS: readonly ZxEditingControlAction[] = [
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

@Component({
  selector: 'zx-release-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent],
  templateUrl: './zx-release-editing-controls.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) releaseUrl!: string;
  @Input() presentation: 'inline' | 'popover' = 'inline';
  @Input() popoverAriaLabel = '';
  @Input() size: 'xs' | 'sm' | 'md' | null = null;

  readonly actions = RELEASE_EDITING_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string =>
    `${this.releaseUrl}id:${elementId}/action:${action}/`;
}
