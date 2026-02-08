import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
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

@Component({
  selector: 'zx-fp-unvoted-tunes',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxTableComponent, ZxTuneRowComponent, ZxSkeletonComponent, ZxCaptionDirective],
  templateUrl: './unvoted-tunes.component.html',
  styleUrls: ['./unvoted-tunes.component.scss']
})
export class UnvotedTunesComponent extends FirstpageModuleBase<ZxTuneDto> {
  title = '';

  constructor(
    private dataService: FirstpageDataService,
    private translate: TranslateService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
    this.translate.get('firstpage.modules.unvotedTunes').subscribe(t => this.title = t);
  }

  protected loadData(): Observable<ZxTuneDto[]> {
    return this.dataService.getUnvotedTunes(this.settings.limit);
  }
}
