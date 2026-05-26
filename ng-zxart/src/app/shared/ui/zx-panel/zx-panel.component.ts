import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {HeadingDirective} from '../typography/directives/heading.directive';

@Component({
  selector: 'zx-panel',
  standalone: true,
  imports: [CommonModule, HeadingDirective],
  templateUrl: './zx-panel.component.html',
  styleUrl: './zx-panel.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPanelComponent {
  @Input() radius: 'sm' | 'md' | 'lg' | 'xl' = 'md';
  @Input() padding: 'none' | 'sm'  | 'md' | 'lg' = 'md';
  @Input() variant: 'elevated' | 'flat' | 'deep' = 'elevated';
  @Input() title = '';
  @Input() titleLevel: 'h2' | 'h3' = 'h3';
  @Input() contentBleed = false;
  @Input() topStripe: 'primary' | 'artist' | 'coder' | null = null;

  @HostBinding('class') get hostClass(): string {
    let classes = `zx-panel--radius-${this.radius} zx-panel--padding-${this.padding} zx-panel--${this.variant}`;
    if (this.contentBleed) classes += ' zx-panel--content-bleed';
    if (this.topStripe) classes += ` zx-panel--stripe-${this.topStripe}`;
    return classes;
  }
}
