import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {HeadingDirective} from '../../typography/directives/heading.directive';

/**
 * Large visual/logical form section with an optional title.
 * Use only when a form has a real titled or structural block.
 */
@Component({
  selector: 'zx-form-section',
  standalone: true,
  imports: [HeadingDirective],
  templateUrl: './zx-form-section.component.html',
  styleUrl: './zx-form-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormSectionComponent {
  @Input() title = '';
}
