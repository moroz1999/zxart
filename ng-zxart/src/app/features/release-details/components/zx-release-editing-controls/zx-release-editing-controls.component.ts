import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const RELEASE_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'showPublicForm', labelKey: 'release-details.edit'},
  {
    action: 'clone',
    privilege: 'clone',
    labelKey: 'release-details.clone',
    color: 'secondary',
    confirm: {messageKey: 'release-details.clone-confirm-message'},
    run: {action: 'clone', targetPath: 'release', failureKey: 'release-details.clone-failed'},
  },
];

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

  readonly editActions = RELEASE_EDIT_ACTIONS;

  readonly buildActionUrl = (_action: string, elementId: number): string => `/release/${elementId}/edit`;
}
