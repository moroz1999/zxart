import {ChangeDetectionStrategy, Component, HostBinding} from '@angular/core';

/**
 * Hero facts row: a wrapping line of short identity facts. Children are
 * `zx-fact` elements, which draw the `·` separator between themselves.
 */
@Component({
  selector: 'zx-facts',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-facts.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFactsComponent {
  @HostBinding('class.app-typography-label') readonly labelClass = true;
  @HostBinding('class.app-typography-tone-muted') readonly mutedClass = true;
}
