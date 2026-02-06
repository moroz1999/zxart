import {Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-panel',
  standalone: true,
  templateUrl: './zx-panel.component.html',
  styleUrl: './zx-panel.component.scss'
})
export class ZxPanelComponent {
  @Input() radius: 'sm' | 'md' | 'lg' | 'xl' = 'md';
  @Input() padding: 'none' | 'sm'  | 'md' | 'lg' = 'md';
  @Input() variant: 'elevated' | 'flat' = 'elevated';

  @HostBinding('class') get hostClass(): string {
    return `zx-panel--radius-${this.radius} zx-panel--padding-${this.padding} zx-panel--${this.variant}`;
  }
}
