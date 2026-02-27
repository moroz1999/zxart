import {Component} from '@angular/core';

/**
 * Internal display component rendered inside the CDK overlay pane by TooltipDirective.
 * Not intended for direct use — use the `[zxTooltip]` directive instead.
 */
@Component({
  selector: 'zx-tooltip-overlay',
  standalone: true,
  templateUrl: './tooltip-overlay.component.html',
  host: {
    'class': 'zx-tooltip',
    '[class.zx-tooltip--visible]': 'visible',
  },
})
export class TooltipOverlayComponent {
  text = '';
  visible = false;
}
