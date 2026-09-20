import {Injectable} from '@angular/core';
import {Dialog, DialogRef} from '@angular/cdk/dialog';
import {EmulatorDialogData, ZxEmulatorDialogComponent} from '../components/zx-emulator-dialog/zx-emulator-dialog.component';

@Injectable({providedIn: 'root'})
export class EmulatorModalService {
  constructor(private dialog: Dialog) {}

  /**
   * Closing the dialog reloads the page, because nothing else unloads an
   * emulator. They are Emscripten modules: the runtime keeps its heap, its
   * audio and its listeners on `window` for as long as the document lives, and
   * neither the engines nor the loaders under `htdocs/libs/` offer a teardown —
   * MAME's own `stop()` is an empty function. Pausing the frame loop is all an
   * engine can do, so without this a played release stays resident and the next
   * one loads a second copy beside it.
   */
  open(data: EmulatorDialogData): DialogRef<void, ZxEmulatorDialogComponent> {
    const ref = this.dialog.open<void, EmulatorDialogData, ZxEmulatorDialogComponent>(
      ZxEmulatorDialogComponent,
      {
        data,
        height: '95vh',
        panelClass: 'zx-dialog',
        backdropClass: 'zx-dialog-backdrop',
      },
    );
    ref.closed.subscribe(() => window.location.reload());

    return ref;
  }
}
