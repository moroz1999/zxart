import {ChangeDetectionStrategy, Component} from '@angular/core';

/**
 * Hero action bar: vote controls in the default slot, counters next to them and
 * primary actions pushed to the trailing edge. Sits in the `zxHeroBar` slot of
 * `zx-hero` and keeps the same order on every entity page.
 */
@Component({
  selector: 'zx-hero-bar',
  standalone: true,
  templateUrl: './zx-hero-bar.component.html',
  styleUrl: './zx-hero-bar.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxHeroBarComponent {}
