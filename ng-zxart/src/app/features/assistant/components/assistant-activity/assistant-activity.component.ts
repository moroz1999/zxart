import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxInsetComponent} from '../../../../shared/ui/zx-inset/zx-inset.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxPreformattedComponent} from '../../../../shared/ui/zx-preformatted/zx-preformatted.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxStepComponent, ZxStepStatus} from '../../../../shared/ui/zx-steps/zx-step.component';
import {ZxStepsComponent} from '../../../../shared/ui/zx-steps/zx-steps.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {AssistantActivityStepView, AssistantActivityView} from '../../models/assistant-view';
import {environment} from '../../../../../environments/environment';

/**
 * The tool calls the assistant made in a row, folded into one line. It is open
 * while the run adds to it, folded once it is done, unless the user chose.
 */
@Component({
  selector: 'zx-assistant-activity',
  standalone: true,
  imports: [
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxInlineComponent,
    ZxInsetComponent,
    ZxPanelComponent,
    ZxPreformattedComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    ZxStepComponent,
    ZxStepsComponent,
    TextDirective,
  ],
  templateUrl: './assistant-activity.component.html',
  styleUrls: ['./assistant-activity.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantActivityComponent {
  @Input({required: true}) activity!: AssistantActivityView;

  /** The user's choice; until they make one the activity follows the run. */
  private chosenOpen: boolean | null = null;
  detailsOpen = false;

  constructor(iconReg: SvgIconRegistryService) {
    for (const icon of ['check', 'expand-more', 'expand-less']) {
      iconReg.loadSvg(`${environment.svgUrl}${icon}.svg`, icon)?.subscribe();
    }
  }

  get open(): boolean {
    return this.chosenOpen ?? this.activity.running;
  }

  toggle(): void {
    this.chosenOpen = !this.open;
  }

  toggleDetails(): void {
    this.detailsOpen = !this.detailsOpen;
  }

  stepStatus(step: AssistantActivityStepView, last: boolean): ZxStepStatus {
    if (step.failed) {
      return 'failed';
    }
    return last && this.activity.running ? 'running' : 'done';
  }
}
