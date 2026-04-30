import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {CommonModule} from '@angular/common';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../zx-button-controls/zx-button-controls.component';
import {ZxHeading2Directive} from '../../directives/typography/typography.directives';

export interface ConfirmDialogData {
  title: string;
  message: string;
  confirmLabel: string;
  cancelLabel: string;
  danger?: boolean;
}

@Component({
  selector: 'zx-confirm-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-confirm-dialog.component.html',
  styleUrl: './zx-confirm-dialog.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxConfirmDialogComponent {
  constructor(
    @Inject(DIALOG_DATA) public data: ConfirmDialogData,
    private dialogRef: DialogRef<boolean, ZxConfirmDialogComponent>,
  ) {}

  confirm(): void {
    this.dialogRef.close(true);
  }

  cancel(): void {
    this.dialogRef.close(false);
  }
}
