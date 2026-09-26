import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {EmulatorModalService} from '../../../features/emulator/services/emulator-modal.service';
import {EmulatorType, toSupportedEmulatorType} from '../../../features/emulator/engines/emulator-engine';
import {ZxButtonComponent} from '../zx-button/zx-button.component';

@Component({
  selector: 'zx-emulator-play-button',
  standalone: true,
  imports: [CommonModule, ZxButtonComponent],
  template: `
    <zx-button
      *ngIf="canPlay"
      color="primary"
      [size]="size"
      [square]="square"
      [ariaLabel]="ariaLabel"
      (click)="onPlay()"
    ><ng-content></ng-content></zx-button>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxEmulatorPlayButtonComponent {
  @Input({required: true}) isPlayable!: boolean;
  @Input({required: true}) isDownloadable!: boolean;
  @Input({required: true}) playUrl!: string | null;
  @Input({required: true}) emulatorType!: string | null;
  @Input() launchFilePath: string | null = null;
  @Input() hardware: string[] | null = null;
  @Input() canUploadScreenshot = false;
  @Input() screenshotUploadElementId: number | null = null;
  @Input() size: 'xs' | 'sm' | 'md' = 'md';
  @Input() square = false;
  @Input() ariaLabel = '';

  constructor(private readonly emulator: EmulatorModalService) {}

  get supportedEmulatorType(): EmulatorType | null {
    return toSupportedEmulatorType(this.emulatorType);
  }

  get canPlay(): boolean {
    return this.isPlayable
      && this.isDownloadable
      && this.playUrl !== null
      && this.supportedEmulatorType !== null;
  }

  onPlay(): void {
    const type = this.supportedEmulatorType;
    if (!type || !this.playUrl) {
      return;
    }
    this.emulator.open({
      emulatorType: type,
      fileUrl: this.playUrl,
      launchFilePath: this.launchFilePath ?? undefined,
      hardware: this.hardware ?? undefined,
      uploadElementId: this.screenshotUploadElementId ?? undefined,
      canScreenshot: this.canUploadScreenshot,
    });
  }
}
