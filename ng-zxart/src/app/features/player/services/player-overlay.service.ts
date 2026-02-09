import {Injectable} from '@angular/core';
import {MatBottomSheet, MatBottomSheetRef} from '@angular/material/bottom-sheet';
import {PlayerService} from './player.service';
import {PlayerSheetComponent} from '../components/player-sheet/player-sheet.component';

@Injectable({
  providedIn: 'root'
})
export class PlayerOverlayService {
  private sheetRef: MatBottomSheetRef<PlayerSheetComponent> | null = null;

  constructor(
    private bottomSheet: MatBottomSheet,
    private playerService: PlayerService,
  ) {
    this.playerService.state$.subscribe(state => {
      if (state.visible) {
        this.open();
      } else {
        this.close();
      }
    });
  }

  private open(): void {
    if (this.sheetRef) {
      return;
    }
    this.sheetRef = this.bottomSheet.open(PlayerSheetComponent, {
      hasBackdrop: false,
    });
  }

  private close(): void {
    if (!this.sheetRef) {
      return;
    }
    this.sheetRef.dismiss();
    this.sheetRef = null;
  }
}
