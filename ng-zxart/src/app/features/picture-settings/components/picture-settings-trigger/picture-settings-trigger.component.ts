import {Component, HostListener, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {Subscription} from 'rxjs';
import {PictureSettingsService} from '../../services/picture-settings.service';
import {PictureMode, PictureSettings} from '../../models/picture-settings';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxBodySmMutedDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-picture-settings-trigger',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxButtonComponent,
    ZxBodySmMutedDirective,
    ZxHeaderPopoverComponent,
  ],
  templateUrl: './picture-settings-trigger.component.html',
  styleUrls: ['./picture-settings-trigger.component.scss'],
})
export class PictureSettingsTriggerComponent implements OnInit, OnDestroy {
  popoverOpen = false;
  settings!: PictureSettings;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
  ];

  readonly modes: {value: PictureMode; labelKey: string}[] = [
    {value: 'mix', labelKey: 'picture-settings.mode.mix'},
    {value: 'flicker', labelKey: 'picture-settings.mode.flicker'},
    {value: 'interlace1', labelKey: 'picture-settings.mode.interlace1'},
    {value: 'interlace2', labelKey: 'picture-settings.mode.interlace2'},
  ];

  private subscription = new Subscription();

  constructor(
    private pictureSettingsService: PictureSettingsService,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}image.svg`, 'image')?.subscribe();
    this.subscription.add(
      this.pictureSettingsService.settings.subscribe(s => {
        this.settings = s;
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.popoverOpen = !this.popoverOpen;
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  setMode(mode: PictureMode): void {
    this.pictureSettingsService.setMode(mode);
  }

  setBorder(border: boolean): void {
    this.pictureSettingsService.setBorder(border);
  }

  setHidden(hidden: boolean): void {
    this.pictureSettingsService.setHidden(hidden);
  }

  isActiveMode(mode: PictureMode): boolean {
    return this.settings.mode === mode;
  }

  @HostListener('document:keydown.escape')
  onEscape(): void {
    if (this.popoverOpen) {
      this.closePopover();
    }
  }
}
