import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxCollapsibleSectionComponent
} from '../../../../shared/ui/zx-collapsible-section/zx-collapsible-section.component';
import {
  ZxSkeletonBoneComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-prod-description',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxCollapsibleSectionComponent, ZxSkeletonBoneComponent],
  templateUrl: './zx-prod-description.component.html',
  styleUrls: ['./zx-prod-description.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDescriptionComponent {
  @Input({required: true}) description!: string | null;
  @Input() htmlDescription = false;

  readonly skeletonLines = [0, 1, 2];
}
