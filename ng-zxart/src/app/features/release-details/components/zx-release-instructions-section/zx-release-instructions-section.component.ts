import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdInstructionFileDto} from '../../../prod-details/models/prod-instruction-file.dto';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInstructionFileCardComponent} from '../../../../shared/ui/zx-instruction-file-card/zx-instruction-file-card.component';

@Component({
  selector: 'zx-release-instructions-section',
  standalone: true,
  imports: [
    CommonModule,
    HeadingDirective,
    TextDirective,
    ZxInlineComponent,
    ZxStackComponent,
    ZxInstructionFileCardComponent,
  ],
  templateUrl: './zx-release-instructions-section.component.html',
  styleUrl: './zx-release-instructions-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseInstructionsSectionComponent {
  @Input({required: true}) instructions!: ProdInstructionFileDto[];

  trackById(_: number, file: ProdInstructionFileDto): number {
    return file.id;
  }
}
