import {
  AfterViewInit,
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  HostBinding,
  Input,
  NgZone,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {NgxImageZoomModule} from 'ngx-image-zoom';
import {Lightbox} from 'ng-gallery/lightbox';
import {Subscription} from 'rxjs';
import {PictureDetailsDto} from '../../models/picture-details.dto';
import {PictureSettingsService} from '../../../picture-settings/services/picture-settings.service';
import {PictureUrlBuilderService} from '../../../../shared/services/picture-url-builder.service';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureMode, PictureScale, PictureSettings} from '../../../picture-settings/models/picture-settings';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

type TriState = 'g' | 'on' | 'off';
type GigaState = 'g' | PictureMode;
type Device = 'phone' | 'tablet' | 'desktop';

const BASE_WIDTH = 320;
const BASE_HEIGHT = 240;
const STANDARD_TYPE = 'standard';
const SCA_TYPE = 'sca';

// Available scale options per device (matches shared/breakpoints: md=768, lg=992).
const SCALES_BY_DEVICE: Record<Device, ReadonlyArray<PictureScale>> = {
  phone: ['1', 'wide'],
  tablet: ['1', '2', 'wide'],
  desktop: ['1', '2', '3', 'wide'],
};

@Component({
  selector: 'zx-picture-viewer',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    NgxImageZoomModule,
    PictureGalleryHostComponent,
    ZxPanelComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxStackComponent,
    ZxInlineComponent,
    TextDirective,
  ],
  templateUrl: './zx-picture-viewer.component.html',
  styleUrls: ['./zx-picture-viewer.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureViewerComponent implements OnInit, AfterViewInit, OnDestroy {
  @Input({required: true}) picture!: PictureDetailsDto;

  @ViewChild('stage') stageRef?: ElementRef<HTMLElement>;

  /** In "wide" mode the viewer takes the full hero row (metadata wraps below). */
  @HostBinding('class.pix-viewer--wide')
  get wideClass(): boolean {
    return !this.scaled;
  }

  readonly galleryId = 'zx-picture-details-viewer';
  readonly scaleOptions: ReadonlyArray<{value: PictureScale; labelKey: string}> = [
    {value: '1', labelKey: 'picture-details.scale-1x'},
    {value: '2', labelKey: 'picture-details.scale-2x'},
    {value: '3', labelKey: 'picture-details.scale-3x'},
    {value: 'wide', labelKey: 'picture-details.scale-wide'},
  ];
  readonly borderOptions: ReadonlyArray<{value: TriState; labelKey: string}> = [
    {value: 'g', labelKey: 'picture-details.render-global'},
    {value: 'on', labelKey: 'picture-details.render-on'},
    {value: 'off', labelKey: 'picture-details.render-off'},
  ];
  readonly gigaOptions: ReadonlyArray<{value: GigaState; labelKey: string}> = [
    {value: 'g', labelKey: 'picture-details.render-global'},
    {value: 'mix', labelKey: 'picture-details.giga-mix'},
    {value: 'flicker', labelKey: 'picture-details.giga-flicker'},
    {value: 'interlace1', labelKey: 'picture-details.giga-interlace'},
    {value: 'interlace2', labelKey: 'picture-details.giga-interlace2'},
  ];
  readonly hiddenOptions = this.borderOptions;
  readonly magnification = 3;

  scale: PictureScale = '3';
  device: Device = 'desktop';
  borderOverride: TriState = 'g';
  gigaOverride: GigaState = 'g';
  hiddenOverride: TriState = 'g';

  imageUrl = '';
  fullImageUrl = '';
  naturalWidth = 0;
  naturalHeight = 0;

  /** Displayed size of the picture block, in CSS pixels. */
  displayWidth = 0;
  displayHeight = 0;
  /** Identity used to recreate the zoom instance whenever the size changes. */
  zoomKey = '';

  private globalSettings: PictureSettings | null = null;
  private subscription?: Subscription;
  private scaleSubscription?: Subscription;
  private resizeObserver?: ResizeObserver;
  private phoneQuery?: MediaQueryList;
  private tabletQuery?: MediaQueryList;
  private readonly onDeviceChange = (): void => {
    this.zone.run(() => {
      this.applyDevice();
      this.cdr.markForCheck();
    });
  };

  constructor(
    private readonly pictureSettingsService: PictureSettingsService,
    private readonly pictureUrlBuilderService: PictureUrlBuilderService,
    private readonly pictureGalleryService: PictureGalleryService,
    private readonly lightbox: Lightbox,
    private readonly zone: NgZone,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  openLightbox(): void {
    this.lightbox.open(0, this.galleryId);
  }

  ngOnInit(): void {
    this.phoneQuery = window.matchMedia('(max-width: 767.98px)');
    this.tabletQuery = window.matchMedia('(min-width: 768px) and (max-width: 991.98px)');
    this.phoneQuery.addEventListener('change', this.onDeviceChange);
    this.tabletQuery.addEventListener('change', this.onDeviceChange);
    this.applyDevice(true);
    this.updateDisplaySize();
    this.pictureGalleryService.ensureGalleryLoaded(this.galleryId, [this.picture]);
    this.subscription = this.pictureSettingsService.settings.subscribe(settings => {
      this.globalSettings = settings;
      this.rebuildUrl();
      this.cdr.markForCheck();
    });
    // Picks up the stored zoom once the logged-in user's preferences arrive.
    this.scaleSubscription = this.pictureSettingsService.scale.subscribe(scale => {
      const next = this.scaleForDevice(scale);
      if (next === this.scale) {
        return;
      }
      this.scale = next;
      this.updateDisplaySize();
      this.cdr.markForCheck();
    });
  }

  ngAfterViewInit(): void {
    // In "wide" mode the block size follows the column width — track it so the
    // zoom instance is rebuilt (and re-measured) whenever it changes.
    this.resizeObserver = new ResizeObserver(() => {
      if (!this.scaled) {
        this.zone.run(() => {
          this.updateDisplaySize();
          this.cdr.markForCheck();
        });
      }
    });
    if (this.stageRef) {
      this.resizeObserver.observe(this.stageRef.nativeElement);
    }
    this.updateDisplaySize();
    this.cdr.markForCheck();
  }

  ngOnDestroy(): void {
    this.subscription?.unsubscribe();
    this.scaleSubscription?.unsubscribe();
    this.resizeObserver?.disconnect();
    this.phoneQuery?.removeEventListener('change', this.onDeviceChange);
    this.tabletQuery?.removeEventListener('change', this.onDeviceChange);
  }

  get isFlickering(): boolean {
    return this.picture.isFlickering;
  }

  get isStandard(): boolean {
    return this.picture.type === STANDARD_TYPE;
  }

  get isSca(): boolean {
    return this.picture.type === SCA_TYPE;
  }

  get hasRenderControls(): boolean {
    return !this.isSca || this.isFlickering || this.isStandard;
  }

  get scaled(): boolean {
    return this.scale !== 'wide';
  }

  get scaleMultiplier(): number {
    return this.scaled ? Number(this.scale) : 0;
  }

  /** Scale buttons available on the current device. */
  get availableScaleOptions(): ReadonlyArray<{value: PictureScale; labelKey: string}> {
    const allowed = SCALES_BY_DEVICE[this.device];
    return this.scaleOptions.filter(option => allowed.includes(option.value));
  }

  /** Detects the device class and keeps the selected scale valid for it. */
  private applyDevice(initial = false): void {
    const device: Device = this.phoneQuery?.matches
      ? 'phone'
      : this.tabletQuery?.matches
        ? 'tablet'
        : 'desktop';
    if (!initial && device === this.device) {
      return;
    }
    this.device = device;
    if (initial) {
      this.scale = this.scaleForDevice(this.pictureSettingsService.currentScale);
    } else if (!SCALES_BY_DEVICE[device].includes(this.scale)) {
      this.scale = 'wide';
    }
    this.updateDisplaySize();
  }

  /**
   * The remembered zoom is shared across devices, so one too large for the
   * current screen (3× on a phone) falls back to the full-width view.
   */
  private scaleForDevice(scale: PictureScale): PictureScale {
    return SCALES_BY_DEVICE[this.device].includes(scale) ? scale : 'wide';
  }

  /** One zoom instance, recreated when its size changes (empty until ready). */
  get zoomInstances(): string[] {
    return this.imageUrl && this.displayWidth ? [this.zoomKey] : [];
  }

  trackZoom(_index: number, key: string): string {
    return key;
  }

  setScale(value: PictureScale): void {
    this.pictureSettingsService.setScale(value);
  }

  setBorder(value: TriState): void {
    this.borderOverride = value;
    this.rebuildUrl();
  }

  setGiga(value: GigaState): void {
    this.gigaOverride = value;
    this.rebuildUrl();
  }

  setHidden(value: TriState): void {
    this.hiddenOverride = value;
    this.rebuildUrl();
  }

  private updateDisplaySize(): void {
    let width: number;
    let height: number;
    if (this.scaled) {
      width = BASE_WIDTH * this.scaleMultiplier;
      height = BASE_HEIGHT * this.scaleMultiplier;
    } else {
      width = Math.round(this.stageRef?.nativeElement.clientWidth ?? 0);
      height = Math.round((width * BASE_HEIGHT) / BASE_WIDTH);
    }
    if (width === this.displayWidth && height === this.displayHeight) {
      return;
    }
    this.displayWidth = width;
    this.displayHeight = height;
    this.zoomKey = `${this.scale}-${width}x${height}`;
  }

  private rebuildUrl(): void {
    if (!this.globalSettings) {
      return;
    }
    const settings = this.effectiveSettings();
    this.imageUrl = this.pictureUrlBuilderService.buildUrl(this.picture, settings, 1);
    // Higher-resolution source for the hover loupe so its zoom math has detail
    // to magnify even when the block is upscaled (e.g. 960px at 3×).
    this.fullImageUrl = this.pictureUrlBuilderService.buildUrl(this.picture, settings, 2);
    this.preloadDimensions(this.imageUrl);
  }

  private preloadDimensions(url: string): void {
    const image = new Image();
    image.onload = () => {
      this.naturalWidth = image.naturalWidth;
      this.naturalHeight = image.naturalHeight;
      this.cdr.markForCheck();
    };
    image.src = url;
  }

  private effectiveSettings(): PictureSettings {
    const base = this.globalSettings!;
    return {
      border: !this.isSca && (this.borderOverride === 'g' ? base.border : this.borderOverride === 'on'),
      hidden: this.isStandard && (this.hiddenOverride === 'g' ? base.hidden : this.hiddenOverride === 'on'),
      mode: this.gigaOverride === 'g' ? base.mode : this.gigaOverride,
    };
  }
}
