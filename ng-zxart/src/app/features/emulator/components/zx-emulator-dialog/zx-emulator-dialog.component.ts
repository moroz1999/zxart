import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  HostListener,
  Inject,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {
  ZxBodySmMutedDirective,
  ZxHeading2Directive,
} from '../../../../shared/directives/typography/typography.directives';
import {EmulatorEngine, EmulatorType} from '../../engines/emulator-engine';
import {UspEngine} from '../../engines/usp.engine';
import {Zx81Engine} from '../../engines/zx81.engine';
import {TsconfEngine} from '../../engines/tsconf.engine';
import {SamcoupeEngine} from '../../engines/samcoupe.engine';
import {ZxNextEngine} from '../../engines/zxnext.engine';
import {EmulatorScreenshotService, UspScreenSelection} from '../../services/emulator-screenshot.service';

export interface EmulatorDialogData {
  emulatorType: EmulatorType;
  fileUrl: string;
  uploadUrl?: string;
  canScreenshot?: boolean;
}

const F2_SCREENSHOT_DELAY_MS = 300;

@Component({
  selector: 'zx-emulator-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxHeading2Directive,
    ZxBodySmMutedDirective,
  ],
  templateUrl: './zx-emulator-dialog.component.html',
  styleUrl: './zx-emulator-dialog.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxEmulatorDialogComponent implements OnInit, OnDestroy {
  @ViewChild('canvas', {static: true}) canvasRef!: ElementRef<HTMLCanvasElement>;

  screenshotSelection: UspScreenSelection = '48';
  loading = true;
  error: string | null = null;

  private engine: EmulatorEngine | null = null;

  constructor(
    @Inject(DIALOG_DATA) public data: EmulatorDialogData,
    private dialogRef: DialogRef<void, ZxEmulatorDialogComponent>,
    private screenshotService: EmulatorScreenshotService,
    private cdr: ChangeDetectorRef,
  ) {}

  get showSamcoupeNote(): boolean {
    return this.data.emulatorType === 'samcoupe';
  }

  get showScreenshotControls(): boolean {
    return this.data.emulatorType === 'usp' && !!this.data.canScreenshot;
  }

  ngOnInit(): void {
    this.engine = this.createEngine(this.data.emulatorType);
    this.engine
      .start(this.canvasRef.nativeElement, this.data.fileUrl)
      .then(() => {
        this.loading = false;
        this.cdr.markForCheck();
      })
      .catch((err: unknown) => {
        this.loading = false;
        this.error = err instanceof Error ? err.message : String(err);
        this.cdr.markForCheck();
      });
  }

  ngOnDestroy(): void {
    this.engine?.destroy();
    this.engine = null;
  }

  setFullscreen(): void {
    this.engine?.setFullscreen();
  }

  close(): void {
    this.dialogRef.close();
  }

  @HostListener('window:keydown.F2', ['$event'])
  onF2(event: KeyboardEvent): void {
    if (!this.showScreenshotControls || !this.data.uploadUrl) {
      return;
    }
    event.preventDefault();
    const uploadUrl = this.data.uploadUrl;
    const fileUrl = this.data.fileUrl;
    const selection = this.screenshotSelection;
    setTimeout(() => {
      this.screenshotService.captureAndUpload(selection, fileUrl, uploadUrl).subscribe({
        error: err => console.error('Emulator screenshot upload failed:', err),
      });
    }, F2_SCREENSHOT_DELAY_MS);
  }

  private createEngine(type: EmulatorType): EmulatorEngine {
    switch (type) {
      case 'usp': return new UspEngine();
      case 'zx81': return new Zx81Engine();
      case 'tsconf': return new TsconfEngine();
      case 'samcoupe': return new SamcoupeEngine();
      case 'zxnext': return new ZxNextEngine();
    }
  }
}
