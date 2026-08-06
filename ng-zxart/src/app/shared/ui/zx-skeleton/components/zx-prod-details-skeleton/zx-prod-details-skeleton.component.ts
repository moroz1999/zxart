import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxScreenshotGridSkeletonComponent} from '../zx-screenshot-grid-skeleton/zx-screenshot-grid-skeleton.component';
import {ZxRowSkeletonComponent} from '../zx-row-skeleton/zx-row-skeleton.component';
import {ZxStackComponent} from '../../../zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../zx-inline/zx-inline.component';
import {ZxPanelComponent} from '../../../zx-panel/zx-panel.component';
import {ZxHeroComponent} from '../../../zx-hero/zx-hero.component';
import {ZxHeroBarComponent} from '../../../zx-hero-bar/zx-hero-bar.component';

/**
 * Loading placeholder for the prod page. It is built from the containers the
 * loaded page uses — `zx-hero` with its action bar, then the body `zx-stack`
 * with its panels and tab bar — so every padding, border and gap around the
 * placeholders is the real one and cannot drift from the page it replaces.
 *
 * The hero opens without media: a prod carries one only when it has a video.
 * The page title is not part of it — that heading lives in `zx-page-layout`'s
 * header, which reserves its line on its own.
 */
@Component({
  selector: 'zx-prod-details-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [
    ZxSkeletonBoneComponent,
    ZxScreenshotGridSkeletonComponent,
    ZxRowSkeletonComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxPanelComponent,
    ZxHeroComponent,
    ZxHeroBarComponent,
  ],
  templateUrl: './zx-prod-details-skeleton.component.html',
  styleUrls: ['./zx-prod-details-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDetailsSkeletonComponent {
  @Input() animated = true;

  readonly chips = [0, 1, 2];
  readonly descriptionLines = [0, 1, 2];
  readonly links = [0, 1];
  readonly peopleLines = [0, 1];
  readonly tabs = [0, 1, 2, 3];
  readonly tags = [0, 1, 2, 3];
}
