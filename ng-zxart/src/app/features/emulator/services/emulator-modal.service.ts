import {Injectable} from '@angular/core';
import {Dialog, DialogRef} from '@angular/cdk/dialog';
import {EmulatorDialogData, ZxEmulatorDialogComponent} from '../components/zx-emulator-dialog/zx-emulator-dialog.component';

@Injectable({providedIn: 'root'})
export class EmulatorModalService {
  constructor(private dialog: Dialog) {}

  open(data: EmulatorDialogData): DialogRef<void, ZxEmulatorDialogComponent> {
    return this.dialog.open<void, EmulatorDialogData, ZxEmulatorDialogComponent>(
      ZxEmulatorDialogComponent,
      {
        data,
        panelClass: 'zx-dialog',
        backdropClass: 'zx-dialog-backdrop',
      },
    );
  }
}
