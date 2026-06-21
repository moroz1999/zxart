import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {NgIf} from '@angular/common';
import {TextDirective} from '../typography/directives/text.directive';

export type CalloutAccent = 'warning' | 'primary' | 'none';
export type CalloutSurface = 'deep' | 'raised';
export type CalloutOrientation = 'row' | 'column';
export type CalloutAlign = 'start' | 'center' | 'stretch';

/**
 * Accent callout: a recessed inset box with a colored left rule. The visual atom
 * behind the rating strip and the picture/tune provenance boxes. Optionally
 * bordered/interactive and turned into a full-area link via `href`, which also
 * covers the release "back to prod" anchor.
 */
@Component({
  selector: 'zx-callout',
  standalone: true,
  imports: [NgIf, TextDirective],
  templateUrl: './zx-callout.component.html',
  styleUrl: './zx-callout.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCalloutComponent {
  @Input() accent: CalloutAccent = 'warning';
  @Input() surface: CalloutSurface = 'deep';
  @Input() orientation: CalloutOrientation = 'column';
  @Input() align: CalloutAlign = 'stretch';
  @Input() wrap = true;
  @Input() bordered = false;
  @Input() interactive = false;
  @Input() label = '';
  @Input() href = '';
  @Input() ariaLabel = '';

  @HostBinding('class') get hostClass(): string {
    return [
      `zx-callout--accent-${this.accent}`,
      `zx-callout--surface-${this.surface}`,
      `zx-callout--${this.orientation}`,
      `zx-callout--align-${this.align}`,
      this.wrap ? 'zx-callout--wrap' : '',
      this.bordered ? 'zx-callout--bordered' : '',
      this.interactive || this.href ? 'zx-callout--interactive' : '',
    ].filter(Boolean).join(' ');
  }
}
