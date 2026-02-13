import {Component, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';

@Component({
  selector: 'zx-button',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-button.component.html',
  styleUrl: './zx-button.component.scss'
})
export class ZxButtonComponent implements OnDestroy {
  @Input() size: 'xs' | 'sm' | 'md' = 'md';
  @Input() color: 'primary' | 'secondary' | 'danger' | 'transparent' | 'outlined' = 'primary';
  @Input() disabled = false;
  @Input() type: 'button' | 'submit' | 'reset' = 'button';
  @Input() href: string | null = null;
  @Input() target: '_self' | '_blank' | '_parent' | '_top' | null = null;
  @Input() rel: string | null = null;
  @Input() square = false;
  @Input() round = false;
  @Input() ariaLabel = '';
  @Input() extraClass = '';
  rippleX = 0;
  rippleY = 0;
  rippleSize = 0;
  rippleActive = false;
  private rippleTimer: ReturnType<typeof setTimeout> | null = null;

  get isLink(): boolean {
    return !!this.href;
  }

  get classList(): string {
    const classes = [`zx-button`, `zx-button--${this.size}`, `zx-button--${this.color}`];
    if (this.disabled) {
      classes.push('zx-button--disabled');
    }
    if (this.square) {
      classes.push('zx-button--square');
    }
    if (this.round) {
      classes.push('zx-button--round');
    }
    if (this.extraClass) {
      classes.push(this.extraClass);
    }
    return classes.join(' ');
  }

  get safeRel(): string | null {
    if (!this.target || this.target !== '_blank') {
      return this.rel;
    }
    const relParts = new Set((this.rel ?? '').split(' ').filter(Boolean));
    relParts.add('noopener');
    relParts.add('noreferrer');
    return Array.from(relParts).join(' ');
  }

  onLinkClick(event: MouseEvent): void {
    if (this.disabled || !this.href) {
      event.preventDefault();
      event.stopImmediatePropagation();
    }
  }

  onPointerDown(event: PointerEvent): void {
    if (this.disabled) {
      return;
    }

    const target = event.currentTarget as HTMLElement | null;
    if (!target) {
      return;
    }

    const rect = target.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    const maxX = Math.max(x, rect.width - x);
    const maxY = Math.max(y, rect.height - y);

    this.rippleX = x;
    this.rippleY = y;
    this.rippleSize = Math.ceil(Math.sqrt(maxX * maxX + maxY * maxY) * 2);
    this.rippleActive = false;

    if (this.rippleTimer) {
      clearTimeout(this.rippleTimer);
    }

    requestAnimationFrame(() => {
      this.rippleActive = true;
      this.rippleTimer = setTimeout(() => {
        this.rippleActive = false;
      }, 560);
    });
  }

  ngOnDestroy(): void {
    if (this.rippleTimer) {
      clearTimeout(this.rippleTimer);
    }
  }
}
