import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {NgIf} from '@angular/common';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {HeadingDirective} from '../typography/directives/heading.directive';
import {TextDirective} from '../typography/directives/text.directive';

/**
 * Hero identification row with a fixed slot order: original title, `#id` badge,
 * year, projected type badges and chips, and the edit control last.
 */
@Component({
  selector: 'zx-hero-title',
  standalone: true,
  imports: [NgIf, ZxBadgeComponent, HeadingDirective, TextDirective],
  templateUrl: './zx-hero-title.component.html',
  styleUrl: './zx-hero-title.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxHeroTitleComponent {
  @Input({required: true}) title!: string;
  @Input() entityId: number | null = null;
  @Input() year: number | string | null = null;

  get hasYear(): boolean {
    return this.year !== null && this.year !== '' && this.year !== 0;
  }
}
