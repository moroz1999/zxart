import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';
import {PlayerService} from '../../../player/services/player.service';

const PLAYLIST_ID = 'firstpage-random-good-tunes';

@Component({
  selector: 'zx-fp-random-good-tunes',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxTableComponent, ZxTuneRowComponent, ZxSkeletonComponent, ZxCaptionDirective],
  templateUrl: './random-good-tunes.component.html',
  styleUrls: ['./random-good-tunes.component.scss']
})
export class RandomGoodTunesComponent extends FirstpageModuleBase<ZxTuneDto> {
  title = '';

  readonly playingTuneId$ = this.playerService.state$.pipe(
    map(state => state.isPlaying && state.playlistId === PLAYLIST_ID
      ? (state.playlist[state.currentIndex]?.id ?? null)
      : null
    )
  );

  constructor(
    private dataService: FirstpageDataService,
    private translate: TranslateService,
    private playerService: PlayerService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
    this.translate.get('firstpage.modules.randomGoodTunes').subscribe(t => this.title = t);
  }

  protected loadData(): Observable<ZxTuneDto[]> {
    return this.dataService.getRandomGoodTunes(this.settings.limit);
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
