import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Inject, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {HttpClient} from '@angular/common/http';
import {NgxExtendedPdfViewerModule} from 'ngx-extended-pdf-viewer';
import {TranslateModule} from '@ngx-translate/core';
import {ZxDialogComponent} from '../zx-dialog/zx-dialog.component';

export interface FileViewerDialogData {
  fileName: string;
  title: string;
  downloadUrl: string;
}

@Component({
  selector: 'zx-file-viewer-dialog',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxDialogComponent, NgxExtendedPdfViewerModule],
  templateUrl: './zx-file-viewer-dialog.component.html',
  styleUrl: './zx-file-viewer-dialog.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFileViewerDialogComponent implements OnInit {
  textContent: string | null = null;
  loading = false;

  constructor(
    @Inject(DIALOG_DATA) public data: FileViewerDialogData,
    private dialogRef: DialogRef<void, ZxFileViewerDialogComponent>,
    private http: HttpClient,
    private cdr: ChangeDetectorRef,
  ) {}

  get isPdf(): boolean {
    return this.data.fileName.toLowerCase().endsWith('.pdf');
  }

  ngOnInit(): void {
    if (!this.isPdf) {
      this.loading = true;
      this.http.get(this.data.downloadUrl, {responseType: 'text'}).subscribe({
        next: content => {
          this.textContent = content;
          this.loading = false;
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.cdr.markForCheck();
        },
      });
    }
  }

  close(): void {
    this.dialogRef.close();
  }
}
