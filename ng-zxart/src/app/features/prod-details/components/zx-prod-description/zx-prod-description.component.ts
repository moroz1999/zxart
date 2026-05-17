import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {
  ZxSkeletonBoneComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {HeadingDirective, TextDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-prod-description',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxSkeletonBoneComponent, HeadingDirective, TextDirective, ZxStackComponent],
  templateUrl: './zx-prod-description.component.html',
  styleUrls: ['./zx-prod-description.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDescriptionComponent {
  @Input({required: true}) description!: string | null;
  @Input() htmlDescription = false;

  readonly skeletonLines = [0, 1, 2];
}
