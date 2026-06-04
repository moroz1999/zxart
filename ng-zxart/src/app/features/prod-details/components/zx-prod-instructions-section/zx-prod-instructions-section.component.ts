import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdInstructionsApiService} from '../../services/prod-instructions-api.service';
import {ProdInstructionFileDto} from '../../models/prod-instruction-file.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInstructionFileCardComponent} from '../../../../entities/zx-instruction-file-card/zx-instruction-file-card.component';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';

@Component({
  selector: 'zx-prod-instructions-section',
  standalone: true,
  imports: [
    CommonModule,
    ZxStackComponent,
    ZxInstructionFileCardComponent,
    InViewportDirective,
  ],
  templateUrl: './zx-prod-instructions-section.component.html',
  styleUrl: './zx-prod-instructions-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInstructionsSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  files: ProdInstructionFileDto[] = [];

  constructor(
    private readonly api: ProdInstructionsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.api.getInstructions(this.elementId).subscribe(files => {
      this.files = files;
      this.loaded = true;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  trackById(_: number, file: ProdInstructionFileDto): number {
    return file.id;
  }
}
