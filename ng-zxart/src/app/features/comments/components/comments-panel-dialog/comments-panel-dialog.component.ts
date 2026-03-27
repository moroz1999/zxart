import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {DialogRef} from '@angular/cdk/dialog';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {LatestCommentsComponent} from '../latest-comments/latest-comments.component';

@Component({
  selector: 'zx-comments-panel-dialog',
  standalone: true,
  imports: [TranslateModule, ZxButtonComponent, LatestCommentsComponent],
  templateUrl: './comments-panel-dialog.component.html',
  styleUrls: ['./comments-panel-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentsPanelDialogComponent {
  constructor(private dialogRef: DialogRef) {}

  close(): void {
    this.dialogRef.close();
  }
}
