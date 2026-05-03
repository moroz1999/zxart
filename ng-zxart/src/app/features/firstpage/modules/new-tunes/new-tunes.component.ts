import {
  ZxTuneTableSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-tune-table-skeleton/zx-tune-table-skeleton.component';
import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
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

const PLAYLIST_ID = 'firstpage-new-tunes';

@Component({
  selector: 'zx-fp-new-tunes',
  standalone: true,
  imports: [
    ZxTuneTableSkeletonComponent,
    CommonModule,
    ZxTableComponent,
    ZxTuneRowComponent,
    FirstpageModuleWrapperComponent,
  ],
  templateUrl: './new-tunes.component.html',
  styleUrls: ['./new-tunes.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class NewTunesComponent extends FirstpageModuleBase<ZxTuneDto> {
  readonly moduleType = 'newTunes' as const;
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
    return this.dataService.getNewTunes(this.settings.limit);
  }

  playTune(index: number): void {
    const selected = this.currentItems[index];
    if (!selected) {
      return;
    }
    const playable = this.currentItems.filter(item => item.isPlayable && item.mp3Url);
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
