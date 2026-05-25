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
  @Input() gap: 'md' | 'lg' = 'md';

  @HostBinding('class.zx-filter-bar--scrollable')
  get isScrollable(): boolean {
    return this.scrollable;
  }

  @HostBinding('class.zx-filter-bar--gap-lg')
  get isLargeGap(): boolean {
    return this.gap === 'lg';
  }
}
