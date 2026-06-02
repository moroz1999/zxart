import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {Dialog} from '@angular/cdk/dialog';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ReleaseFileStructureItemDto} from '../../models/release-details.dto';
import {ZxReleaseSectionHeadComponent} from '../zx-release-section-head/zx-release-section-head.component';
import {environment} from '../../../../../environments/environment';
import {
  FileViewerDialogData,
  ZxFileViewerDialogComponent,
} from '../../../../shared/ui/zx-file-viewer-dialog/zx-file-viewer-dialog.component';
import {ReleaseDetailsApiService} from '../../services/release-details-api.service';
import {TapeAudioService} from '../../services/tape-audio.service';

@Component({
  selector: 'zx-release-file-structure',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxButtonComponent, ZxReleaseSectionHeadComponent, SvgIconComponent],
  templateUrl: './zx-release-file-structure.component.html',
  styleUrl: './zx-release-file-structure.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseFileStructureComponent implements OnInit {
  @Input({required: true}) releaseId!: number;
  @Input({required: true}) items!: ReleaseFileStructureItemDto[];

  constructor(
    private readonly iconReg: SvgIconRegistryService,
    private readonly dialog: Dialog,
    private readonly api: ReleaseDetailsApiService,
    readonly tapeAudio: TapeAudioService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}folder.svg`, 'folder')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}file.svg`, 'file')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}zip.svg`, 'zip')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}eye.svg`, 'eye')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}play.svg`, 'play')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}pause.svg`, 'pause')?.subscribe();
  }

  getIcon(item: ReleaseFileStructureItemDto): string {
    if (item.type === 'folder') return 'folder';
    if (item.type === 'zip') return 'zip';
    return 'file';
  }

  countItems(items: ReleaseFileStructureItemDto[]): number {
    return items.reduce((acc, item) => acc + 1 + (item.items?.length ? this.countItems(item.items) : 0), 0);
  }

  range(n: number): number[] {
    return Array.from({length: n});
  }

  downloadFile(item: ReleaseFileStructureItemDto): void {
    if (!item.downloadUrl) {
      return;
    }

    window.location.assign(item.downloadUrl);
  }

  canPlayAudio(item: ReleaseFileStructureItemDto): boolean {
    return this.tapeAudio.isPlayableFormat(item.type) && !!item.downloadUrl;
  }

  getAudioUrl(item: ReleaseFileStructureItemDto): string | null {
    if (!item.downloadUrl) {
      return null;
    }

    if (item.downloadUrl.includes('/play:1/')) {
      return item.downloadUrl;
    }

    return item.downloadUrl.replace(/\/([^/?#]+)([?#].*)?$/, '/play:1/$1$2');
  }

  playAudio(item: ReleaseFileStructureItemDto): void {
    const audioUrl = this.getAudioUrl(item);

    if (!audioUrl) {
      return;
    }

    void this.tapeAudio.toggle(audioUrl, item.type);
  }

  openViewer(item: ReleaseFileStructureItemDto): void {
    if (item.type !== 'file' || !item.viewable) {
      return;
    }

    const data: FileViewerDialogData = {
      fileName: item.fileName,
      title: item.fileName,
      contentUrl: this.api.getFileContentUrl(this.releaseId, item.id),
      contentType: 'html',
    };

    this.dialog.open<void, FileViewerDialogData, ZxFileViewerDialogComponent>(
      ZxFileViewerDialogComponent,
      {
        data,
        width: '92vw',
        maxWidth: '1440px',
        height: '86vh',
        panelClass: 'zx-dialog',
        backdropClass: 'zx-dialog-backdrop',
      },
    );
  }
}
