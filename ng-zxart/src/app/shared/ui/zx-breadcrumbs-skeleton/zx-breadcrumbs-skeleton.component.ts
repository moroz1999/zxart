import {ChangeDetectionStrategy, Component, HostBinding} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxSkeletonVisibilityDirective} from '../zx-skeleton/zx-skeleton-visibility.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';

/**
 * Loading placeholder for `zx-breadcrumbs`, rendered by `zx-breadcrumb-bar`
 * until the trail of the current page is known.
 *
 * It mirrors the trail exactly: the same row of items and `/` separators, the
 * same gap, and bones whose box is a real line of breadcrumb text — each one
 * wraps a hidden word, so its height is the line box of an item at the shared
 * font size and the page below never moves when the trail replaces it. The
 * words also give the bones the widths of a plausible trail; they are hidden
 * and never announced, so they carry no meaning and are not translated.
 */
@Component({
  selector: 'zx-breadcrumbs-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [CommonModule, ZxSkeletonBoneComponent],
  templateUrl: './zx-breadcrumbs-skeleton.component.html',
  styleUrl: './zx-breadcrumbs-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbsSkeletonComponent {
  readonly items = ['ZX-Art', 'Section', 'Current page'];

  @HostBinding('attr.aria-hidden') readonly ariaHidden = 'true';
}
