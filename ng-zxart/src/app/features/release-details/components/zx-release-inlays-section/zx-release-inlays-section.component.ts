import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdReleaseInlayDto} from '../../../prod-details/models/prod-release-inlay.dto';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxInlayTileComponent} from '../../../../shared/ui/zx-inlay-tile/zx-inlay-tile.component';

@Component({
  selector: 'zx-release-inlays-section',
  standalone: true,
  imports: [
    CommonModule,
    HeadingDirective,
    TextDirective,
    ZxInlineComponent,
    ZxInlayTileComponent,
  ],
  templateUrl: './zx-release-inlays-section.component.html',
  styleUrl: './zx-release-inlays-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseInlaysSectionComponent {
  @Input({required: true}) inlays!: ProdReleaseInlayDto[];

  openDownload(url: string): void {
    window.open(url, '_blank', 'noopener');
  }

  trackById(_: number, inlay: ProdReleaseInlayDto): number {
    return inlay.id;
  }
}
