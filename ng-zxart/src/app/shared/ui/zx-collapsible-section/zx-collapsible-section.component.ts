import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxHeading2Directive} from '../../directives/typography/typography.directives';

@Component({
  selector: 'zx-collapsible-section',
  standalone: true,
  imports: [CommonModule, ZxHeading2Directive],
  templateUrl: './zx-collapsible-section.component.html',
  styleUrl: './zx-collapsible-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCollapsibleSectionComponent {
  @Input() title = '';
  @Input() open = false;
}
