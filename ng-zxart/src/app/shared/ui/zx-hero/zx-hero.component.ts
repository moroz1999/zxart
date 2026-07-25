import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

/** Width token of the hero media column. `none` collapses the hero to one column. */
export type ZxHeroMedia = 'none' | 'auto' | 'half' | 'xs' | 'sm' | 'md' | 'lg' | 'xl';

/**
 * Entity hero shell: the panel every detail page opens with. It owns the
 * `media | body` grid, the body rhythm and the full-width action bar rail, so
 * the seven entity pages share one container instead of hand-rolled grids.
 */
@Component({
  selector: 'zx-hero',
  standalone: true,
  templateUrl: './zx-hero.component.html',
  styleUrl: './zx-hero.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxHeroComponent {
  @Input() media: ZxHeroMedia = 'auto';

  @HostBinding('class')
  get hostClass(): string {
    return `zx-hero--media-${this.media}`;
  }
}
