import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatButtonModule} from '@angular/material/button';

@Component({
  selector: 'zx-button',
  standalone: true,
  imports: [CommonModule, MatButtonModule],
  templateUrl: './zx-button.component.html',
  styleUrl: './zx-button.component.scss'
})
export class ZxButtonComponent {
  @Input() size: 'xs' | 'sm' | 'md' = 'md';
  @Input() color: 'primary' | 'secondary' | 'danger' = 'primary';
  @Input() disabled = false;
  @Input() type: 'button' | 'submit' | 'reset' = 'button';
  @Input() square = false;

  get classList(): string {
    const classes = [`zx-button`, `zx-button--${this.size}`, `zx-button--${this.color}`];
    if (this.square) {
      classes.push('zx-button--square');
    }
    return classes.join(' ');
  }
}
