import {ChangeDetectionStrategy, Component} from '@angular/core';
import {AsyncPipe} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {environment} from '../../../../../environments/environment';
import {AssistantPanelService} from '../../services/assistant-panel.service';

/** The header button that opens and closes the assistant's drawer. */
@Component({
  selector: 'zx-assistant-trigger',
  standalone: true,
  imports: [AsyncPipe, TranslateModule, SvgIconComponent, ZxButtonComponent],
  templateUrl: './assistant-trigger.component.html',
  styleUrls: ['./assistant-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantTriggerComponent {
  constructor(
    readonly panel: AssistantPanelService,
    iconReg: SvgIconRegistryService,
  ) {
    iconReg.loadSvg(`${environment.svgUrl}sparkles.svg`, 'sparkles')?.subscribe();
  }
}
