import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ReleaseDetailsDto} from '../../models/release-details.dto';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxEmulatorPlayButtonComponent} from '../../../../shared/ui/zx-emulator-play-button/zx-emulator-play-button.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-release-action-bar',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxInlineComponent,
    TextDirective,
    ZxEmulatorPlayButtonComponent,
    ZxItemControlsComponent,
  ],
  templateUrl: './zx-release-action-bar.component.html',
  styleUrl: './zx-release-action-bar.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseActionBarComponent {
  @Input({required: true}) details!: ReleaseDetailsDto;

  constructor(private readonly iconReg: SvgIconRegistryService) {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }
}
