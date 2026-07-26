import {ChangeDetectionStrategy, Component} from '@angular/core';

/**
 * One item of a `zx-facts` row. Renders its own leading `·` separator unless it
 * is the first fact actually present in the row.
 */
@Component({
  selector: 'zx-fact',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-fact.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFactComponent {}
