import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

@Component({
  selector: 'zx-stack',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-stack.component.html',
  styleUrl: './zx-stack.component.scss',
  host: {
    '[class]': 'classList'
  }
})
export class ZxStackComponent {
  @Input() spacing: 'sm' | 'md' | 'lg' | 'xl' | 'xxl' = 'md';
  @Input() direction: 'column' | 'row' = 'column';

  get classList(): string {
    return `zx-stack--${this.direction} zx-stack--spacing-${this.spacing}`;
  }
}
