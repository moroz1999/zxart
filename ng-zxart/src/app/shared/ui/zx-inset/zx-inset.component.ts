import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-inset',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-inset.component.html',
  styleUrl: './zx-inset.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxInsetComponent {
  @Input() padding: 'none' | 'xs' | 'sm' | 'md' | 'lg' | 'xl' = 'md';
  @Input() side: 'all' | 'block' | 'inline' | 'top' | 'right' | 'bottom' | 'left' = 'all';

  @HostBinding('class')
  get classList(): string {
    return `zx-inset--padding-${this.padding} zx-inset--side-${this.side}`;
  }
}
