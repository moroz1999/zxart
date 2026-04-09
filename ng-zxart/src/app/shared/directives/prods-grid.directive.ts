import {Directive, HostBinding} from '@angular/core';

/**
 * Layout directive for a responsive prods grid.
 *
 * Apply to any block element that contains `zx-prod-block` items.
 * Renders a CSS Grid with auto-fill columns of 256px, collapsing to a single
 * column on small screens.
 *
 * Usage:
 *   <div zxProdsGrid>
 *     <zx-prod-block *ngFor="let prod of items" ...></zx-prod-block>
 *   </div>
 */
@Directive({
  selector: '[zxProdsGrid]',
  standalone: true,
})
export class ZxProdsGridDirective {
  @HostBinding('class.prods-grid') readonly hostClass = true;
}
