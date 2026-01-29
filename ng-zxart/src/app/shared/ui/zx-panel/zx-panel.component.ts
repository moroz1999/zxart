import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

@Component({
  selector: 'zx-panel',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-panel.component.html',
  styleUrl: './zx-panel.component.scss'
})
export class ZxPanelComponent {
  @Input() radius: 'sm' | 'md' | 'lg' | 'xl' = 'md';
  @Input() padding: 'sm'  | 'md' | 'lg' = 'md';

  get classList(): string {
    return `zx-panel zx-panel--radius-${this.radius} zx-panel--padding-${this.padding}`;
  }
}
