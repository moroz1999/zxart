import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxSkeletonBoneComponent} from '../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxSkeletonVisibilityDirective} from '../../shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

/**
 * Loading placeholder for a list of `zx-article-preview` cards. It reuses the
 * card's own panel and stack, so padding, radius and item spacing cannot drift
 * from the real list; only the inner text lines are bones.
 *
 * The optional parts of the card are opt-in: a press mention leads with the
 * publication cover and ends with a read link, a production's own article has
 * neither.
 */
@Component({
  selector: 'zx-article-preview-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [
    CommonModule,
    ZxPanelComponent,
    ZxSkeletonBoneComponent,
    ZxStackComponent,
  ],
  templateUrl: './zx-article-preview-skeleton.component.html',
  styleUrl: './zx-article-preview-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxArticlePreviewSkeletonComponent {
  @Input() count = 3;
  @Input() withImage = false;
  @Input() withReadLink = false;

  get items(): number[] {
    return Array.from({length: this.count}, (_, index) => index);
  }
}
