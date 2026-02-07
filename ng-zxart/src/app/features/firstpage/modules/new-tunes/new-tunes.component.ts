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
  selector: 'app-fp-new-tunes',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxTableComponent, ZxTuneRowComponent, ZxSkeletonComponent, ZxCaptionDirective],
  template: `
    <zx-table [title]="title">
      <zx-skeleton *ngIf="loading" variant="row" [count]="settings.limit"></zx-skeleton>
      <div *ngIf="error" class="module-error" zxCaption>{{ 'firstpage.error' | translate }}</div>
      <div *ngIf="!loading && !error && items.length === 0" class="module-empty" zxCaption>{{ 'firstpage.empty' | translate }}</div>
      <table *ngIf="!loading && !error && items.length > 0" class="tunes-table">
        <tbody>
          <zx-tune-row *ngFor="let tune of items; index as i" [tune]="tune" [index]="i"></zx-tune-row>
        </tbody>
      </table>
    </zx-table>
  `,
  styles: [`.tunes-table { width: 100%; border-collapse: collapse; }`]
})
export class NewTunesComponent extends FirstpageModuleBase<ZxTuneDto> {
  title = '';

  constructor(
    private dataService: FirstpageDataService,
    private translate: TranslateService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
    this.translate.get('firstpage.modules.newTunes').subscribe(t => this.title = t);
  }

  protected loadData(): Observable<ZxTuneDto[]> {
    return this.dataService.getNewTunes(this.settings.limit);
  }
}
