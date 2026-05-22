import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReleaseDetailsDto} from '../../models/release-details.dto';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxEmulatorPlayButtonComponent} from '../../../../shared/ui/zx-emulator-play-button/zx-emulator-play-button.component';

@Component({
  selector: 'zx-release-action-bar',
  standalone: true,
  imports: [
    CommonModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxInlineComponent,
    HeadingDirective,
    TextDirective,
    ZxEmulatorPlayButtonComponent,
  ],
  templateUrl: './zx-release-action-bar.component.html',
  styleUrl: './zx-release-action-bar.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseActionBarComponent {
  @Input({required: true}) details!: ReleaseDetailsDto;
}
