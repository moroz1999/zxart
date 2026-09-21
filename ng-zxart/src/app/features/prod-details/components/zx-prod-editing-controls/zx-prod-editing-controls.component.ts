import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
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
  {
    action: 'pressArticle.publicReceive',
    privilege: 'pressArticle.publicReceive',
    labelKey: 'prod-details.addpressarticle',
    color: 'secondary',
  },
];

/** Offered only for prods linked to a Spectrum Computing entry. */
const IMPORT_SC_SCREENSHOTS_ACTION: ZxEditingControlAction = {
  action: 'importScScreenshots',
  privilege: 'importScScreenshots',
  labelKey: 'prod-details.import-sc-screenshots',
  color: 'secondary',
  run: {
    action: 'importScScreenshots',
    successKey: 'prod-details.import-sc-screenshots-done',
    failureKey: 'prod-details.import-sc-screenshots-failed',
    reloadOnSuccess: true,
  },
};

@Component({
  selector: 'zx-prod-editing-controls',
  standalone: true,
  imports: [ZxEditingControlsComponent, TranslateModule],
  templateUrl: './zx-prod-editing-controls.component.html',
  styleUrl: './zx-prod-editing-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdEditingControlsComponent implements OnChanges {
  @Input({required: true}) elementId!: number;
  @Input() hasZxdbEntry = false;

  readonly editActions = PROD_EDIT_ACTIONS;
  addActions: readonly ZxEditingControlAction[] = PROD_ADD_ACTIONS;

  ngOnChanges(): void {
    this.addActions = this.hasZxdbEntry ? [...PROD_ADD_ACTIONS, IMPORT_SC_SCREENSHOTS_ACTION] : PROD_ADD_ACTIONS;
  }

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

  readonly buildAddActionUrl = (action: string, elementId: number): string =>
    action === 'pressArticle.publicReceive'
      ? `/prod/${elementId}/articles/add`
      : `/prod/${elementId}/releases/add`;
}
