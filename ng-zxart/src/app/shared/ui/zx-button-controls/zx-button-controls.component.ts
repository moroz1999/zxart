import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-button-controls',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-button-controls.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxButtonControlsComponent {
  @Input() align: 'start' | 'end' | 'distribute' | 'fill' | 'full' = 'start';
  @Input() wrap = false;
  @Input() gap: 'sm' | 'md' = 'md';

  @HostBinding('class') get hostClass(): string {
    const classes = [`zx-button-controls--${this.align}`, `zx-button-controls--gap-${this.gap}`];
    if (this.wrap) {
      classes.push('zx-button-controls--wrap');
    }
    return classes.join(' ');
  }
}
