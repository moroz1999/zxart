import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PROD_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'showPublicForm', labelKey: 'prod-details.edit'},
  {action: 'showAiForm', privilege: 'showAiForm', labelKey: 'prod-details.showAiForm', color: 'secondary'},
  {action: 'showJoinForm', privilege: 'showJoinForm', labelKey: 'prod-details.join', color: 'secondary'},
  {action: 'showSplitForm', privilege: 'showSplitForm', labelKey: 'prod-details.split', color: 'secondary'},
];

const PROD_ADD_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'zxRelease.publicAdd',
    privilege: 'zxRelease.publicAdd',
    labelKey: 'prod-details.addrelease',
    color: 'secondary',
  },
];

@Component({
  selector: 'zx-prod-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-prod-editing-controls.component.html',
  styleUrl: './zx-prod-editing-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) addReleaseUrl!: string;

  readonly editActions = PROD_EDIT_ACTIONS;
  readonly addActions = PROD_ADD_ACTIONS;

  readonly buildActionUrl = (action: string, elementId: number): string => {
    switch (action) {
      case 'showAiForm':
        return `/prod/${elementId}/ai`;
      case 'showJoinForm':
        return `/prod/${elementId}/join`;
      case 'showSplitForm':
        return `/prod/${elementId}/split`;
      default:
        return `/prod/${elementId}/edit`;
    }
  };

  readonly buildAddActionUrl = (): string => this.addReleaseUrl;
}
