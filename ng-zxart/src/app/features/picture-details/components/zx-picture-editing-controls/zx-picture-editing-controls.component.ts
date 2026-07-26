import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PICTURE_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'showPublicForm', labelKey: 'picture-details.edit'},
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

  readonly editActions = PICTURE_EDIT_ACTIONS;

  readonly buildActionUrl = (_action: string, elementId: number): string => `/picture/${elementId}/edit`;
}
