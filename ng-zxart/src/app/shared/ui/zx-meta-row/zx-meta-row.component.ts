import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TextDirective} from '../typography/directives/text.directive';

/**
 * Hero service row: a right-aligned label column followed by projected content
 * ("Groups:", "Aliases:", "Links:"). Stacks the label above the content on
 * narrow viewports.
 */
@Component({
  selector: 'zx-meta-row',
  standalone: true,
  imports: [TextDirective],
  templateUrl: './zx-meta-row.component.html',
  styleUrl: './zx-meta-row.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxMetaRowComponent {
  /** Already translated label; the colon is added by the component. */
  @Input({required: true}) label!: string;
}
