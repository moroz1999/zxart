import {ChangeDetectionStrategy, Component} from '@angular/core';
import {DialogRef} from '@angular/cdk/dialog';
import {ZxDialogComponent} from '../../../../shared/ui/zx-dialog/zx-dialog.component';
import {LatestCommentsComponent} from '../latest-comments/latest-comments.component';

@Component({
  selector: 'zx-comments-panel-dialog',
  standalone: true,
  imports: [ZxDialogComponent, LatestCommentsComponent],
  templateUrl: './comments-panel-dialog.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentsPanelDialogComponent {
  constructor(private dialogRef: DialogRef) {}

  close(): void {
    this.dialogRef.close();
  }
}
