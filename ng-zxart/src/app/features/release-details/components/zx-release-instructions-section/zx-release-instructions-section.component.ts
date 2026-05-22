import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdInstructionFileDto} from '../../../prod-details/models/prod-instruction-file.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInstructionFileCardComponent} from '../../../../shared/ui/zx-instruction-file-card/zx-instruction-file-card.component';
import {ZxReleaseSectionHeadComponent} from '../zx-release-section-head/zx-release-section-head.component';

@Component({
  selector: 'zx-release-instructions-section',
  standalone: true,
  imports: [
    CommonModule,
    ZxStackComponent,
    ZxInstructionFileCardComponent,
    ZxReleaseSectionHeadComponent,
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
