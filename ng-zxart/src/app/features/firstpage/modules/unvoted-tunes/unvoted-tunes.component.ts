import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';
import {PlayerService} from '../../../player/services/player.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';

const PLAYLIST_ID = 'firstpage-unvoted-tunes';

@Component({
  selector: 'zx-fp-unvoted-tunes',
  standalone: true,
  imports: [CommonModule, ZxTableComponent, ZxTuneRowComponent, FirstpageModuleWrapperComponent],
  templateUrl: './unvoted-tunes.component.html',
  styleUrls: ['./unvoted-tunes.component.scss']
})
export class UnvotedTunesComponent extends FirstpageModuleBase<ZxTuneDto> {
  readonly moduleType = 'unvotedTunes' as const;
  readonly playingTuneId$ = this.playerService.state$.pipe(
    map(state => state.isPlaying && state.playlistId === PLAYLIST_ID
      ? (state.playlist[state.currentIndex]?.id ?? null)
      : null
    )
  );

  constructor(
    private dataService: FirstpageDataService,
    private playerService: PlayerService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxTuneDto[]> {
    return this.dataService.getUnvotedTunes(this.settings.limit);
  }

  playTune(index: number): void {
    const selected = this.items[index];
    if (!selected) {
      return;
    }
    const playable = this.items.filter(item => item.isPlayable && item.mp3Url);
    const startIndex = playable.findIndex(item => item.id === selected.id);
    if (startIndex === -1) {
      return;
    }
    this.playerService.startPlaylist(PLAYLIST_ID, playable, startIndex);
  }

  pauseTune(): void {
    this.playerService.pause();
  }
}
