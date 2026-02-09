import {Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {PlayerService} from '../../../player/services/player.service';
import {RadioPresetCriteriaService} from '../../../player/services/radio-preset-criteria.service';
import {RadioPreset} from '../../../player/models/radio-preset';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-radio-remote',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    MatButtonModule,
    ZxStackComponent,
  ],
  templateUrl: './radio-remote.component.html',
  styleUrl: './radio-remote.component.scss',
})
export class RadioRemoteComponent {
  presets: {key: RadioPreset; label: string}[] = [
    {key: 'discover', label: 'player.preset.discover'},
    {key: 'randomgood', label: 'player.preset.randomgood'},
    {key: 'games', label: 'player.preset.games'},
    {key: 'demoscene', label: 'player.preset.demoscene'},
    {key: 'lastyear', label: 'player.preset.lastyear'},
    {key: 'ay', label: 'player.preset.ay'},
    {key: 'beeper', label: 'player.preset.beeper'},
    {key: 'exotic', label: 'player.preset.exotic'},
    {key: 'underground', label: 'player.preset.underground'},
  ];

  constructor(
    private playerService: PlayerService,
    private presetCriteriaService: RadioPresetCriteriaService,
  ) {}

  startPreset(preset: RadioPreset): void {
    const criteria = this.presetCriteriaService.buildCriteria(preset);
    this.playerService.startRadio(criteria, preset);
  }
}
