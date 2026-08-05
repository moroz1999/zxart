import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PRESS_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'showPublicForm',
    privilege: 'showPublicForm',
    labelKey: 'press-details.edit',
  },
  {
    action: 'showAiForm',
    privilege: 'showAiForm',
    labelKey: 'press-details.ai',
    color: 'secondary',
  },
];

@Component({
  selector: 'zx-press-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-press-editing-controls.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPressEditingControlsComponent {
  @Input({required: true}) elementId!: number;

  readonly editActions = PRESS_EDIT_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string =>
    action === 'showAiForm' ? `/press/${elementId}/ai` : `/press/${elementId}/edit`;
}
