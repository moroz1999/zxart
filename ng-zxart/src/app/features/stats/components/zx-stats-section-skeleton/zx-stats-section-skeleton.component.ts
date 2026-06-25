import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {
  ZxSkeletonBoneComponent,
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-stats-section-skeleton',
  standalone: true,
  imports: [CommonModule, ZxPanelComponent, ZxGridComponent, ZxStackComponent, ZxSkeletonBoneComponent],
  templateUrl: './zx-stats-section-skeleton.component.html',
  styleUrl: './zx-stats-section-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsSectionSkeletonComponent {
  readonly kpis = [0, 1, 2];

  @Input() variant: 'summary' | 'chart-lg' | 'chart-sm' | 'top-table' = 'chart-lg';
  @Input() interactiveLegend = false;
  @Input() rowCount = 10;

  get tableRows(): unknown[] {
    return Array.from({length: this.rowCount});
  }
}
