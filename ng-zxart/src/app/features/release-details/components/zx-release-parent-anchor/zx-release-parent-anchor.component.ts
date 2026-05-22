import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ReleaseProdRefDto} from '../../models/release-details.dto';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-release-parent-anchor',
  standalone: true,
  imports: [
    CommonModule,
    TextDirective,
    ZxInlineComponent,
    ZxStackComponent,
  ],
  templateUrl: './zx-release-parent-anchor.component.html',
  styleUrl: './zx-release-parent-anchor.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseParentAnchorComponent {
  @Input({required: true}) prod!: ReleaseProdRefDto;
}
