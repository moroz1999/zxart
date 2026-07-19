import {ChangeDetectionStrategy, Component} from '@angular/core';
import {BestTunesOfMonthComponent} from '../../../firstpage/modules/best-tunes-of-month/best-tunes-of-month.component';
import {NewTunesComponent} from '../../../firstpage/modules/new-tunes/new-tunes.component';
import {UnvotedTunesComponent} from '../../../firstpage/modules/unvoted-tunes/unvoted-tunes.component';
import {MODULE_SETTINGS} from '../../../firstpage/models/module-settings.token';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-music-home',
  standalone: true,
  imports: [
    ZxStackComponent,
    NewTunesComponent,
    UnvotedTunesComponent,
    BestTunesOfMonthComponent,
  ],
  providers: [
    {provide: MODULE_SETTINGS, useValue: {limit: 10}},
  ],
  templateUrl: './music-home.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MusicHomeComponent {}
