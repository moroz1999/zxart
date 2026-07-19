import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import type {PartyListViewMode} from '../zx-parties-list/zx-parties-list.component';
import {ZxGridComponent} from '../../shared/ui/zx-grid/zx-grid.component';
import {ZxInlineComponent} from '../../shared/ui/zx-inline/zx-inline.component';
import {ZxInsetComponent} from '../../shared/ui/zx-inset/zx-inset.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxSkeletonBoneComponent} from '../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxSkeletonVisibilityDirective} from '../../shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';

@Component({
  selector: 'zx-parties-list-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [
    CommonModule,
    TranslateModule,
    ZxGridComponent,
    ZxInlineComponent,
    ZxInsetComponent,
    ZxPanelComponent,
    ZxSkeletonBoneComponent,
    ZxTableComponent,
  ],
  templateUrl: './zx-parties-list-skeleton.component.html',
  styleUrl: './zx-parties-list-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartiesListSkeletonComponent {
  @Input() viewMode: PartyListViewMode = 'cards';
  readonly items = Array.from({length: 20}, (_, index) => index);
}
