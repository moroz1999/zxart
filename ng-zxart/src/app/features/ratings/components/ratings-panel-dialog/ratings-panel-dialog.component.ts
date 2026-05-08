import {ChangeDetectionStrategy, Component} from '@angular/core';
import {DialogRef} from '@angular/cdk/dialog';
import {ZxDialogComponent} from '../../../../shared/ui/zx-dialog/zx-dialog.component';
import {RecentRatingsWidgetComponent} from '../recent-ratings-widget/recent-ratings-widget.component';

@Component({
  selector: 'zx-ratings-panel-dialog',
  standalone: true,
  imports: [ZxDialogComponent, RecentRatingsWidgetComponent],
  templateUrl: './ratings-panel-dialog.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RatingsPanelDialogComponent {
  constructor(private dialogRef: DialogRef) {}

  close(): void {
    this.dialogRef.close();
  }
}
