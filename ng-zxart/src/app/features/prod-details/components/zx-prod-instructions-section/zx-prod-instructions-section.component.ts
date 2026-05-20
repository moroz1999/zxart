import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Dialog} from '@angular/cdk/dialog';
import {ProdInstructionsApiService} from '../../services/prod-instructions-api.service';
import {ProdInstructionFileDto} from '../../models/prod-instruction-file.dto';
import {
  FileViewerDialogData,
  ZxProdFileViewerDialogComponent,
} from '../zx-prod-file-viewer-dialog/zx-prod-file-viewer-dialog.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ProdReleaseLabelPipe} from '../../pipes/prod-release-label.pipe';

@Component({
  selector: 'zx-prod-instructions-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxButtonComponent,
    ZxTableComponent,
    TextDirective,
    InViewportDirective,
    ProdReleaseLabelPipe,
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
    private readonly dialog: Dialog,
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

  isViewable(file: ProdInstructionFileDto): boolean {
    const ext = file.fileName.toLowerCase().split('.').pop() ?? '';
    return ext !== 'zip';
  }

  openViewer(file: ProdInstructionFileDto): void {
    const data: FileViewerDialogData = {
      fileName: file.fileName,
      title: file.title || file.fileName,
      downloadUrl: file.downloadUrl,
    };
    this.dialog.open<void, FileViewerDialogData, ZxProdFileViewerDialogComponent>(
      ZxProdFileViewerDialogComponent,
      {
        data,
        width: '90vw',
        maxWidth: '1200px',
        panelClass: 'zx-dialog',
        backdropClass: 'zx-dialog-backdrop',
      },
    );
  }
}
