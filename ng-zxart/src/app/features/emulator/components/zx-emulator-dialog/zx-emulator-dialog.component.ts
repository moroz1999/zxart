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
import {ZxDialogComponent} from '../../../../shared/ui/zx-dialog/zx-dialog.component';
import {ZxExtLinksComponent, ZxExtLinkDto} from '../../../../shared/ui/zx-ext-links/zx-ext-links.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {EmulatorEngine, EmulatorType} from '../../engines/emulator-engine';
import {UspEngine} from '../../engines/usp.engine';
import {Zx81Engine} from '../../engines/zx81.engine';
import {TsconfEngine} from '../../engines/tsconf.engine';
import {SamcoupeEngine} from '../../engines/samcoupe.engine';
import {ZxNextEngine} from '../../engines/zxnext.engine';
import {JsSpeccyEngine} from '../../engines/jsspeccy.engine';
import {EMULATOR_HOMEPAGES, EmulatorHomepage} from '../../models/emulator-homepage';
import {EmulatorScreenshotService, UspScreenSelection} from '../../services/emulator-screenshot.service';
import {AnalyticsService} from '../../../../shared/services/analytics.service';

export interface EmulatorDialogData {
  emulatorType: EmulatorType;
  fileUrl: string;
  /** Prod or release element the captured screenshot is attached to. */
  uploadElementId?: number;
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
    ZxDialogComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxExtLinksComponent,
    TextDirective,
  ],
  templateUrl: './zx-emulator-dialog.component.html',
  styleUrl: './zx-emulator-dialog.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxEmulatorDialogComponent implements OnInit, OnDestroy {
  @ViewChild('canvas', {static: true}) canvasRef!: ElementRef<HTMLCanvasElement>;
  @ViewChild('canvasWrap', {static: true}) canvasWrapRef!: ElementRef<HTMLElement>;

  screenshotSelection: UspScreenSelection = '48';
  loading = true;
  error: string | null = null;
  /** The emulator's own home page, credited at the bottom of the dialog. */
  homepageLinks: ZxExtLinkDto[] = [];

  private engine: EmulatorEngine | null = null;

  constructor(
    @Inject(DIALOG_DATA) public data: EmulatorDialogData,
    private dialogRef: DialogRef<void, ZxEmulatorDialogComponent>,
    private screenshotService: EmulatorScreenshotService,
    private cdr: ChangeDetectorRef,
    private analytics: AnalyticsService,
  ) {}

  get showSamcoupeNote(): boolean {
    return this.data.emulatorType === 'samcoupe';
  }

  /** The engine draws its own interface in the wrapper, so the canvas stays out of the way. */
  get rendersOwnUi(): boolean {
    return this.engine?.rendersOwnUi ?? false;
  }

  get showScreenshotControls(): boolean {
    return this.data.emulatorType === 'usp' && !!this.data.canScreenshot;
  }

  ngOnInit(): void {
    const homepage: EmulatorHomepage | null = EMULATOR_HOMEPAGES[this.data.emulatorType];
    this.homepageLinks = homepage ? [{url: homepage.url, label: homepage.name}] : [];
    this.engine = this.createEngine(this.data.emulatorType);
    this.engine
      .start(this.canvasRef.nativeElement, this.data.fileUrl, this.canvasWrapRef.nativeElement)
      .then(() => {
        this.loading = false;
        this.cdr.markForCheck();
        this.analytics.reachGoal('emulatorstart');
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
    if (!this.data.uploadElementId || !this.data.canScreenshot) {
      return;
    }
    event.preventDefault();
    const elementId = this.data.uploadElementId;

    if (this.showScreenshotControls) {
      const fileUrl = this.data.fileUrl;
      const selection = this.screenshotSelection;
      setTimeout(() => {
        this.screenshotService.captureAndUpload(selection, fileUrl, elementId).subscribe({
          error: err => console.error('Emulator screenshot upload failed:', err),
        });
      }, F2_SCREENSHOT_DELAY_MS);
      return;
    }

    if (this.engine?.captureScreenshot) {
      setTimeout(() => {
        this.engine!.captureScreenshot!('standard').then(blob => {
          if (!blob) {
            return;
          }
          this.screenshotService.uploadBlob(blob, elementId, 's81').subscribe({
            error: err => console.error('Emulator screenshot upload failed:', err),
          });
        });
      }, F2_SCREENSHOT_DELAY_MS);
    }
  }

  private createEngine(type: EmulatorType): EmulatorEngine {
    switch (type) {
      case 'usp': return new UspEngine();
      case 'zx81': return new Zx81Engine();
      case 'tsconf': return new TsconfEngine();
      case 'samcoupe': return new SamcoupeEngine();
      case 'zxnext': return new ZxNextEngine();
      case 'timex2048':
      case 'timex2068': return new JsSpeccyEngine(type);
    }
  }
}
