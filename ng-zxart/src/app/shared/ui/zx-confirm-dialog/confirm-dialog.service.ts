import {Injectable} from '@angular/core';
import {Dialog} from '@angular/cdk/dialog';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {ConfirmDialogData, ZxConfirmDialogComponent} from './zx-confirm-dialog.component';

@Injectable({providedIn: 'root'})
export class ConfirmDialogService {
  constructor(private dialog: Dialog) {}

  confirm(data: ConfirmDialogData): Observable<boolean> {
    const dialogRef = this.dialog.open<boolean, ConfirmDialogData, ZxConfirmDialogComponent>(
      ZxConfirmDialogComponent,
      {
        data,
        panelClass: 'zx-dialog',
        backdropClass: 'zx-dialog-backdrop',
      },
    );
    return dialogRef.closed.pipe(map(result => result === true));
  }
}
