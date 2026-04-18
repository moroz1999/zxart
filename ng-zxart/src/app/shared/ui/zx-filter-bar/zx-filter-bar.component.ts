import {ChangeDetectionStrategy, Component} from '@angular/core';

@Component({
  selector: 'zx-filter-bar',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-filter-bar.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFilterBarComponent {}
