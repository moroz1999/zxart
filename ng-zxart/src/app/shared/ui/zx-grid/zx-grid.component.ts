import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

type GridColumns = '1' | '2' | '3' | '4' | '5' | '6';
type GridRows = 'auto' | '1' | '2' | '3' | '4';
type GridGap = 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' | 'xxl';
type GridAlignment = 'start' | 'center' | 'end' | 'stretch';

@Component({
  selector: 'zx-grid',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-grid.component.html',
  styleUrl: './zx-grid.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGridComponent {
  @Input() desktopColumns: GridColumns = '1';
  @Input() mobileColumns: GridColumns | null = null;
  @Input() tabletColumns: GridColumns | null = null;

  @Input() rows: GridRows = 'auto';

  @Input() gap: GridGap = 'md';
  @Input() rowGap: GridGap | null = null;
  @Input() columnGap: GridGap | null = null;

  @Input() align: GridAlignment = 'stretch';
  @Input() justify: GridAlignment = 'stretch';

  @HostBinding('style.--zx-grid-columns')
  get hostColumns(): GridColumns {
    return this.desktopColumns;
  }

  @HostBinding('style.--zx-grid-mobile-columns')
  get hostMobileColumns(): GridColumns {
    return this.mobileColumns ?? this.tabletColumns ?? this.desktopColumns;
  }

  @HostBinding('style.--zx-grid-tablet-columns')
  get hostTabletColumns(): GridColumns {
    return this.tabletColumns ?? this.desktopColumns;
  }

  @HostBinding('class')
  get classList(): string {
    return [
      `zx-grid--rows-${this.rows}`,
      `zx-grid--row-gap-${this.rowGap ?? this.gap}`,
      `zx-grid--column-gap-${this.columnGap ?? this.gap}`,
      `zx-grid--align-${this.align}`,
      `zx-grid--justify-${this.justify}`,
    ].join(' ');
  }
}
