import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

export type ZxMedalVariant = 'gold' | 'silver' | 'bronze' | 'outlined';

@Component({
  selector: 'zx-medal',
  standalone: true,
  templateUrl: './zx-medal.component.html',
  styleUrl: './zx-medal.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxMedalComponent {
  @Input() variant: ZxMedalVariant = 'outlined';

  @HostBinding('class')
  get hostClass(): string {
    return `zx-medal--${this.variant}`;
  }
}
