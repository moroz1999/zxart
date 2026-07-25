import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

const PARTY_EDIT_ACTIONS: readonly ZxEditingControlAction[] = [
  {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'party-details.action.showPublicForm'},
];

const PARTY_ADD_ACTIONS: readonly ZxEditingControlAction[] = [
  {
    action: 'picturesUploadForm.batchUploadForm',
    privilege: 'picturesUploadForm.batchUploadForm',
    labelKey: 'party-details.action.upload-pictures',
    color: 'secondary',
  },
  {
    action: 'musicUploadForm.batchUploadForm',
    privilege: 'musicUploadForm.batchUploadForm',
    labelKey: 'party-details.action.upload-music',
    color: 'secondary',
  },
  {
    action: 'zxProdsUploadForm.batchUploadForm',
    privilege: 'zxProdsUploadForm.batchUploadForm',
    labelKey: 'party-details.action.upload-prods',
    color: 'secondary',
  },
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
  @Input({required: true}) addActionBaseUrl!: string;

  readonly editActions = PARTY_EDIT_ACTIONS;
  readonly addActions = PARTY_ADD_ACTIONS;

  readonly buildActionUrl = (_action: string, elementId: number): string => `/party/${elementId}/edit`;

  readonly buildAddActionUrl = (action: string): string => {
    const separatorIndex = action.indexOf('.');
    const type = action.substring(0, separatorIndex);
    const legacyAction = action.substring(separatorIndex + 1);

    return `${this.addActionBaseUrl}type:${type}/action:${legacyAction}/`;
  };
}
