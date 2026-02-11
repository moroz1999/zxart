import {Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxHeading2Directive, ZxHeading3Directive} from '../../directives/typography/typography.directives';

@Component({
  selector: 'zx-panel',
  standalone: true,
  imports: [CommonModule, ZxHeading2Directive, ZxHeading3Directive],
  templateUrl: './zx-panel.component.html',
  styleUrl: './zx-panel.component.scss'
})
export class ZxPanelComponent {
  @Input() radius: 'sm' | 'md' | 'lg' | 'xl' = 'md';
  @Input() padding: 'none' | 'sm'  | 'md' | 'lg' = 'md';
  @Input() variant: 'elevated' | 'flat' = 'elevated';
  @Input() title = '';
  @Input() titleLevel: 'h2' | 'h3' = 'h3';

  @HostBinding('class') get hostClass(): string {
    return `zx-panel--radius-${this.radius} zx-panel--padding-${this.padding} zx-panel--${this.variant}`;
  }
}
