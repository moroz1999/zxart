import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {NgTemplateOutlet} from '@angular/common';

export type ZxChipVariant = 'opaque' | 'filled';
export type ZxChipColor = 'neutral' | 'primary' | 'artist' | 'code' | 'intro';
export type ZxChipSize = 'sm' | 'md';

@Component({
  selector: 'zx-chip',
  standalone: true,
  imports: [NgTemplateOutlet],
  templateUrl: './zx-chip.component.html',
  styleUrl: './zx-chip.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxChipComponent {
  @Input() variant: ZxChipVariant = 'opaque';
  @Input() color: ZxChipColor = 'neutral';
  @Input() size: ZxChipSize = 'md';
  @Input() href: string | null = null;

  @HostBinding('class')
  get hostClass(): string { return 'zx-chip-host'; }

  get chipClass(): string {
    return `zx-chip zx-chip--${this.variant} zx-chip--${this.color} zx-chip--${this.size}`;
  }

  get isLink(): boolean { return this.href !== null; }
}
