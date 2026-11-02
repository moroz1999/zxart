import {ChangeDetectionStrategy, Component, inject, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ReleaseDataApiService} from '../../../../shared/api/release-data-api.service';
import {
  ZxEditingControlAction,
  ZxEditingControlsComponent,
} from '../../../../shared/ui/zx-editing-controls/zx-editing-controls.component';

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

  private readonly releaseDataApi = inject(ReleaseDataApiService);

  readonly editActions: readonly ZxEditingControlAction[] = [
    {action: 'showPublicForm', privilege: 'publicReceive', labelKey: 'release-details.edit'},
    {
      action: 'clone',
      privilege: 'clone',
      labelKey: 'release-details.clone',
      color: 'secondary',
      confirm: {messageKey: 'release-details.clone-confirm-message'},
      run: {execute: id => this.releaseDataApi.clone(id), targetPath: 'release', failureKey: 'release-details.clone-failed'},
    },
  ];

  readonly buildActionUrl = (_action: string, elementId: number): string => `/release/${elementId}/edit`;
}
