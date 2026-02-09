import {Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {PlayerService} from '../../services/player.service';
import {PlayerSheetComponent} from '../player-sheet/player-sheet.component';

@Component({
  selector: 'zx-player',
  standalone: true,
  imports: [CommonModule, PlayerSheetComponent],
  templateUrl: './player-host.component.html',
  styleUrls: ['./player-host.component.scss'],
})
export class PlayerHostComponent {
  state$ = this.playerService.state$;

  constructor(private playerService: PlayerService) {}
}
