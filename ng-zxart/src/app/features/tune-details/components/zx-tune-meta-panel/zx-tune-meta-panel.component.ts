import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {TuneDetailsDto} from '../../models/tune-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxItemDataItemComponent} from '../../../../shared/ui/zx-item-data/zx-item-data-item.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {TechSettingRow, ZxTechSettingsComponent} from '../../../../shared/ui/zx-tech-settings/zx-tech-settings.component';
import {BackendLinksService} from '../../../header/services/backend-links.service';

@Component({
  selector: 'zx-tune-meta-panel',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxItemDataItemComponent,
    ZxStackComponent,
    TextDirective,
    ZxTechSettingsComponent,
  ],
  templateUrl: './zx-tune-meta-panel.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTuneMetaPanelComponent {
  @Input({required: true}) tune!: TuneDetailsDto;

  readonly musicBaseUrl$: Observable<string | null> = this.backendLinks.links$.pipe(
    map(links => links.musicBaseUrl),
  );

  constructor(
    private readonly backendLinks: BackendLinksService,
    private readonly translate: TranslateService,
  ) {}

  get techRows(): TechSettingRow[] {
    const rows: TechSettingRow[] = [];
    if (this.tune.frequency) {
      rows.push({label: this.translate.instant('tune-details.frequency'), value: this.tune.frequency});
    }
    if (this.tune.intFrequency) {
      rows.push({label: this.translate.instant('tune-details.int-frequency'), value: this.tune.intFrequency});
    }
    if (this.tune.fileName) {
      rows.push({label: this.translate.instant('tune-details.file-name'), value: this.tune.fileName});
    }
    if (this.tune.converterVersion) {
      rows.push({label: this.translate.instant('tune-details.converter'), value: `ZXTune r${this.tune.converterVersion}`});
    }
    return rows;
  }
}
