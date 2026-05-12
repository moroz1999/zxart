import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

@Component({
  selector: 'zx-stack',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-stack.component.html',
  styleUrl: './zx-stack.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStackComponent {
  @Input() gap: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' = 'md';
  @Input() spacing: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' | null = null;
  @Input() align: 'center' | 'start' | 'end' | 'stretch' | null = null;

  @HostBinding('class')
  get classList(): string {
    const classes = [`zx-stack--gap-${this.spacing ?? this.gap}`];
    if (this.align) {
      classes.push(`zx-stack--align-${this.align}`);
    }
    return classes.join(' ');
  }
}
