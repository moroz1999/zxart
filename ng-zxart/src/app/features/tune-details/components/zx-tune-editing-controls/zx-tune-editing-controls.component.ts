import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const TUNE_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'showPublicForm', labelKey: 'tune-details.edit'},
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

  readonly editActions = TUNE_EDIT_ACTIONS;

  readonly buildActionUrl = (_action: string, elementId: number): string => `/tune/${elementId}/edit`;
}
