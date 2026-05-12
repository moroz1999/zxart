import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-grid',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-grid.component.html',
  styleUrl: './zx-grid.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGridComponent {
  @Input() columns: '1' | '2' | '3' | '4' | '5' | '6' = '1';
  @Input() rows: 'auto' | '1' | '2' | '3' | '4' = 'auto';
  @Input() gap: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' = 'md';
  @Input() rowGap: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' | null = null;
  @Input() columnGap: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl' | null = null;
  @Input() align: 'start' | 'center' | 'end' | 'stretch' | null = null;

  @HostBinding('class')
  get classList(): string {
    const classes = [
      `zx-grid--columns-${this.columns}`,
      `zx-grid--rows-${this.rows}`,
      `zx-grid--row-gap-${this.rowGap ?? this.gap}`,
      `zx-grid--column-gap-${this.columnGap ?? this.gap}`,
    ];
    if (this.align) {
      classes.push(`zx-grid--align-${this.align}`);
    }
    return classes.join(' ');
  }
}
