import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-filter-bar',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-filter-bar.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFilterBarComponent {
  @Input() scrollable = false;

  @HostBinding('class.zx-filter-bar--scrollable')
  get isScrollable(): boolean {
    return this.scrollable;
  }
}
