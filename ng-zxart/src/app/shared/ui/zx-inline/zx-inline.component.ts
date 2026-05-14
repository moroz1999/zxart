import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-inline',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-inline.component.html',
  styleUrl: './zx-inline.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxInlineComponent {
  @Input() gap: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' = 'md';
  @Input() spacing: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' | null = null;
  @Input() align: 'center' | 'start' | 'end' | 'stretch' | 'baseline' | null = null;
  @Input() justify: 'start' | 'center' | 'end' | 'between' | null = null;
  @Input() wrap = false;

  @HostBinding('class')
  get classList(): string {
    const classes = [`zx-inline--gap-${this.spacing ?? this.gap}`];
    if (this.align) {
      classes.push(`zx-inline--align-${this.align}`);
    }
    if (this.justify) {
      classes.push(`zx-inline--justify-${this.justify}`);
    }
    if (this.wrap) {
      classes.push('zx-inline--wrap');
    }
    return classes.join(' ');
  }
}
