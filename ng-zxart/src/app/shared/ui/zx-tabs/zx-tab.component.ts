import { ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input } from '@angular/core';

@Component({
  selector: 'zx-tab',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTabComponent {
  @Input() label = '';
  @Input() count?: number;

  @HostBinding('class.zx-tab--hidden') hidden = true;

  constructor(private readonly cdr: ChangeDetectorRef) {}

  setActive(active: boolean): void {
    this.hidden = !active;
    this.cdr.markForCheck();
  }
}
