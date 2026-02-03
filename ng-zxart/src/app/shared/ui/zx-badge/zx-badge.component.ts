import {Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

@Component({
  selector: 'zx-badge',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-badge.component.html',
  styleUrl: './zx-badge.component.scss'
})
export class ZxBadgeComponent {
  @Input() variant: 'primary' | 'secondary' = 'primary';

  @HostBinding('class')
  get hostClass(): string {
    return `zx-badge--${this.variant}`;
  }
}
