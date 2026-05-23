import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {EmulatorModalService} from '../../../features/emulator/services/emulator-modal.service';
import {EmulatorType} from '../../../features/emulator/engines/emulator-engine';
import {ZxButtonComponent} from '../zx-button/zx-button.component';

const SUPPORTED_EMULATOR_TYPES: ReadonlyArray<EmulatorType> = ['usp', 'zx81', 'tsconf', 'samcoupe', 'zxnext'];

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
  @Input() canUploadScreenshot = false;
  @Input() screenshotUploadUrl = '';
  @Input() size: 'xs' | 'sm' | 'md' = 'md';
  @Input() square = false;
  @Input() ariaLabel = '';

  constructor(private readonly emulator: EmulatorModalService) {}

  get supportedEmulatorType(): EmulatorType | null {
    const type = this.emulatorType;
    if (!type) {
      return null;
    }
    return SUPPORTED_EMULATOR_TYPES.includes(type as EmulatorType) ? (type as EmulatorType) : null;
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
      uploadUrl: this.screenshotUploadUrl || undefined,
      canScreenshot: this.canUploadScreenshot,
    });
  }
}
