import {ChangeDetectionStrategy, Component, Inject, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {HttpClient} from '@angular/common/http';
import {NgxExtendedPdfViewerModule} from 'ngx-extended-pdf-viewer';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {catchError, map, startWith} from 'rxjs/operators';
import {ZxDialogComponent} from '../zx-dialog/zx-dialog.component';

export interface FileViewerDialogData {
  fileName: string;
  title: string;
  downloadUrl?: string;
  contentUrl?: string;
  contentType?: 'text' | 'html';
}

interface ReleaseFileContentDto {
  contentHtml: string | null;
}

interface FileViewerContentState {
  loading: boolean;
  content: string | null;
  error: boolean;
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
  content$: Observable<FileViewerContentState> = of({loading: true, content: null, error: false});

  constructor(
    @Inject(DIALOG_DATA) public data: FileViewerDialogData,
    private dialogRef: DialogRef<void, ZxFileViewerDialogComponent>,
    private http: HttpClient,
  ) {}

  get isPdf(): boolean {
    return this.data.downloadUrl !== undefined && this.data.fileName.toLowerCase().endsWith('.pdf');
  }

  get isHtml(): boolean {
    return this.data.contentType === 'html';
  }

  ngOnInit(): void {
    if (this.isPdf) {
      return;
    }

    if (this.data.contentUrl !== undefined) {
      this.content$ = this.http.get<ReleaseFileContentDto>(this.data.contentUrl).pipe(
        map(response => ({loading: false, content: response.contentHtml, error: response.contentHtml === null})),
        startWith({loading: true, content: null, error: false}),
        catchError(() => of({loading: false, content: null, error: true})),
      );
      return;
    }

    if (this.data.downloadUrl !== undefined) {
      this.content$ = this.http.get(this.data.downloadUrl, {responseType: 'text'}).pipe(
        map(content => ({loading: false, content, error: false})),
        startWith({loading: true, content: null, error: false}),
        catchError(() => of({loading: false, content: null, error: true})),
      );
      return;
    }

    this.content$ = of({loading: false, content: null, error: true});
  }

  close(): void {
    this.dialogRef.close();
  }
}
