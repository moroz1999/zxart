import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {TuneDetailsDto} from '../../models/tune-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

/**
 * Left hero column — the in-page "player".
 *
 * Static placeholder for now: the oscilloscope / spectral analyser and live
 * playback transport are wired separately later. The CTA and progress are shown
 * inert so the page layout reads true against the prototype.
 */
@Component({
  selector: 'zx-tune-player',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxButtonComponent,
    TextDirective,
  ],
  templateUrl: './zx-tune-player.component.html',
  styleUrls: ['./zx-tune-player.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTunePlayerComponent {
  @Input({required: true}) tune!: TuneDetailsDto;
}
