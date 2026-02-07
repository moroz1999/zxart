import {Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-button-controls',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-button-controls.component.scss'
})
export class ZxButtonControlsComponent {
  @Input() align: 'start' | 'end' | 'distribute' = 'end';

  @HostBinding('class') get hostClass(): string {
    return `zx-button-controls--${this.align}`;
  }
}
