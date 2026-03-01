import {Directive, HostBinding} from '@angular/core';

/**
 * Layout directive for a responsive picture grid.
 *
 * Apply to any block element that contains `zx-picture-card` items.
 * Renders a CSS Grid with auto-fill columns of 320px, collapsing to a single
 * column on small screens.
 *
 * Usage:
 *   <div zxPicturesGrid>
 *     <zx-picture-card *ngFor="let pic of items" ...></zx-picture-card>
 *   </div>
 */
@Directive({
  selector: '[zxPicturesGrid]',
  standalone: true,
})
export class ZxPicturesGridDirective {
  @HostBinding('class.pictures-grid') readonly hostClass = true;
}
