import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

@Component({
  selector: 'zx-stack',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-stack.component.html',
  styleUrl: './zx-stack.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
  host: {
    '[class]': 'classList'
  }
})
export class ZxStackComponent {
  @Input() spacing: 'sm' | 'md' | 'lg' | 'xl' | 'xxl' = 'md';
  @Input() direction: 'column' | 'row' = 'column';
  @Input() align: 'center' | 'start' | 'end' | 'stretch' | null = null;

  get classList(): string {
    const classes = [`zx-stack--${this.direction}`, `zx-stack--spacing-${this.spacing}`];
    if (this.align) {
      classes.push(`zx-stack--align-${this.align}`);
    }
    return classes.join(' ');
  }
}
