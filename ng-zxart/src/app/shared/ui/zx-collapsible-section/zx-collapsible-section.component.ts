import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {HeadingDirective} from '../typography/directives/heading.directive';

@Component({
  selector: 'zx-collapsible-section',
  standalone: true,
  imports: [CommonModule, HeadingDirective],
  templateUrl: './zx-collapsible-section.component.html',
  styleUrl: './zx-collapsible-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCollapsibleSectionComponent {
  @Input() title = '';
  @Input() open = false;
}
