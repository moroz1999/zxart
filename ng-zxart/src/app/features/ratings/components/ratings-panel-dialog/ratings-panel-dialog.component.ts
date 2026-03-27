import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {DialogRef} from '@angular/cdk/dialog';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {RecentRatingsWidgetComponent} from '../recent-ratings-widget/recent-ratings-widget.component';

@Component({
  selector: 'zx-ratings-panel-dialog',
  standalone: true,
  imports: [TranslateModule, ZxButtonComponent, RecentRatingsWidgetComponent],
  templateUrl: './ratings-panel-dialog.component.html',
  styleUrls: ['./ratings-panel-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RatingsPanelDialogComponent {
  constructor(private dialogRef: DialogRef) {}

  close(): void {
    this.dialogRef.close();
  }
}
