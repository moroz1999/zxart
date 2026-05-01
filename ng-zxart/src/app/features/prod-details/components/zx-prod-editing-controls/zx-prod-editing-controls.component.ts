import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {firstValueFrom} from 'rxjs';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ConfirmDialogService} from '../../../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {ProdPrivilegesDto} from '../../models/prod-core.dto';

@Component({
  selector: 'zx-prod-editing-controls',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxButtonComponent],
  templateUrl: './zx-prod-editing-controls.component.html',
  styleUrls: ['./zx-prod-editing-controls.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdEditingControlsComponent {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) prodUrl!: string;
  @Input({required: true}) privileges!: ProdPrivilegesDto;

  constructor(
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
  ) {}

  get hasMainActions(): boolean {
    const p = this.privileges;
    return p.showPublicForm || p.showAiForm || p.resize || p.join || p.split || p.publicDelete;
  }

  get hasAddActions(): boolean {
    return this.privileges.addRelease || this.privileges.addPressArticle;
  }

  actionUrl(action: string): string {
    return `${this.prodUrl}id:${this.elementId}/action:${action}/`;
  }

  addActionUrl(type: string): string {
    return `${this.prodUrl}type:${type}/action:showPublicForm/`;
  }

  async confirmDelete(event: Event): Promise<void> {
    event.preventDefault();
    const data = await firstValueFrom(this.translate.get([
      'prod-details.delete-confirm-title',
      'prod-details.delete-confirm-message',
      'prod-details.delete-confirm-yes',
      'prod-details.delete-confirm-cancel',
    ]));
    const confirmed = await firstValueFrom(this.confirmDialog.confirm({
      title: data['prod-details.delete-confirm-title'],
      message: data['prod-details.delete-confirm-message'],
      confirmLabel: data['prod-details.delete-confirm-yes'],
      cancelLabel: data['prod-details.delete-confirm-cancel'],
      danger: true,
    }));
    if (confirmed) {
      window.location.href = this.actionUrl('publicDelete');
    }
  }
}
