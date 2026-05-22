import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Dialog} from '@angular/cdk/dialog';
import {ZxInlineComponent} from '../zx-inline/zx-inline.component';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {TextDirective} from '../typography/directives/text.directive';
import {FileViewerDialogData, ZxFileViewerDialogComponent} from '../zx-file-viewer-dialog/zx-file-viewer-dialog.component';
import {ProdInstructionFileDto} from '../../../features/prod-details/models/prod-instruction-file.dto';
import {ProdReleaseLabelPipe} from '../../../features/prod-details/pipes/prod-release-label.pipe';

@Component({
  selector: 'zx-instruction-file-card',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxInlineComponent,
    ZxButtonComponent,
    TextDirective,
    ProdReleaseLabelPipe,
  ],
  templateUrl: './zx-instruction-file-card.component.html',
  styleUrl: './zx-instruction-file-card.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxInstructionFileCardComponent {
  @Input({required: true}) file!: ProdInstructionFileDto;
  @Input() showReleaseInfo = false;

  constructor(private readonly dialog: Dialog) {}

  getExt(fileName: string): string {
    return fileName.split('.').pop()?.toUpperCase() ?? 'FILE';
  }

  isViewable(): boolean {
    const ext = this.file.fileName.toLowerCase().split('.').pop() ?? '';
    return ext !== 'zip';
  }

  openViewer(): void {
    const data: FileViewerDialogData = {
      fileName: this.file.fileName,
      title: this.file.title || this.file.fileName,
      downloadUrl: this.file.downloadUrl,
    };
    this.dialog.open<void, FileViewerDialogData, ZxFileViewerDialogComponent>(
      ZxFileViewerDialogComponent,
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
