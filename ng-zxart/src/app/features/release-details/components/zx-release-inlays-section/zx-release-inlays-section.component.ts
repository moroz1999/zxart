import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdReleaseInlayDto} from '../../../prod-details/models/prod-release-inlay.dto';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxInlayTileComponent} from '../../../../shared/ui/zx-inlay-tile/zx-inlay-tile.component';
import {ZxReleaseSectionHeadComponent} from '../zx-release-section-head/zx-release-section-head.component';

@Component({
  selector: 'zx-release-inlays-section',
  standalone: true,
  imports: [
    CommonModule,
    ZxGridComponent,
    ZxInlayTileComponent,
    ZxReleaseSectionHeadComponent,
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
