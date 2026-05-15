import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-release-type-badge',
  standalone: true,
  templateUrl: './zx-release-type-badge.component.html',
  styleUrl: './zx-release-type-badge.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseTypeBadgeComponent {
  @Input({required: true}) type!: string;
  @Input() label: string | null = null;

  @HostBinding('class')
  get hostClass(): string {
    return `zx-release-type-badge--${this.type}`;
  }
}
