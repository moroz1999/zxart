import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

/**
 * Renders text that carries its own line and column layout — scene-era
 * descriptions and magazine copy typed against a fixed-width grid.
 *
 * The `pre` element belongs to the view, never to the stored value: the API
 * returns the text without presentation markup, and this component is what
 * decides to preserve its whitespace.
 *
 * `html` switches to sanitized markup for sources that mix formatting into the
 * text; the whitespace and the fixed-width face stay the same either way.
 */
@Component({
  selector: 'zx-preformatted',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-preformatted.component.html',
  styleUrl: './zx-preformatted.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPreformattedComponent {
  @Input({required: true}) content = '';
  @Input() html = false;
}
