import {AnimationEvent, trigger} from '@angular/animations';
import {
  ChangeDetectionStrategy,
  Component,
  ElementRef,
  HostListener,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {EmulatorModalService} from '../../features/emulator/services/emulator-modal.service';
import {EmulatorType} from '../../features/emulator/engines/emulator-engine';
import {ProdReleaseDto} from '../../features/prod-details/models/prod-release.dto';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxProdLanguageLinksComponent} from '../../features/prod-details/components/zx-prod-language-links/zx-prod-language-links.component';
import {ZxInlineComponent} from '../../shared/ui/zx-inline/zx-inline.component';
import {ZxInsetComponent} from '../../shared/ui/zx-inset/zx-inset.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {environment} from '../../../environments/environment';
import {ZxReleaseTypeBadgeComponent} from '../../shared/ui/zx-release-type-badge/zx-release-type-badge.component';
import {ZxItemControlsComponent} from '../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxBadgeComponent} from '../../shared/ui/zx-badge/zx-badge.component';
import {ZxCardScreenshotGalleryComponent} from '../../shared/ui/zx-card-screenshot-preview/zx-card-screenshot-gallery.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {FadeInOut} from '../../shared/animations/fade-in-out';
import {SlideInOut} from '../../shared/animations/slide-in-out';

const SUPPORTED_EMULATOR_TYPES: ReadonlyArray<EmulatorType> = ['usp', 'zx81', 'tsconf', 'samcoupe', 'zxnext'];

@Component({
  selector: 'zx-prod-release-card',
  standalone: true,
  animations: [
    trigger('fadeInOut', FadeInOut),
    trigger('slideInOut', SlideInOut),
  ],
  imports: [
    CommonModule,
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
    ZxProdLanguageLinksComponent,
    ZxInlineComponent,
    ZxInsetComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxReleaseTypeBadgeComponent,
    ZxItemControlsComponent,
    ZxBadgeComponent,
    ZxCardScreenshotGalleryComponent,
    HeadingDirective,
    TextDirective,
  ],
  templateUrl: './zx-prod-release-card.component.html',
  styleUrls: ['./zx-prod-release-card.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdReleaseCardComponent implements OnChanges, OnInit {
  @Input({required: true}) release!: ProdReleaseDto;
  @Input({required: true}) canUploadScreenshot!: boolean;
  @Input({required: true}) screenshotUploadUrl!: string;

  screenshotUrls: string[] = [];
  displayAdditions = false;
  displayScreenshots = false;
  slideOpenInProgress = false;
  slideCloseInProgress = false;

  constructor(
    private readonly emulator: EmulatorModalService,
    private readonly iconReg: SvgIconRegistryService,
    private readonly element: ElementRef,
  ) {}

  @HostListener('pointerenter', ['$event'])
  enterHandler(event: PointerEvent): void {
    event.preventDefault();
    this.displayAdditions = true;
    if (this.screenshotUrls.length > 0) {
      this.displayScreenshots = true;
    }
  }

  @HostListener('pointerleave', ['$event'])
  leaveHandler(event: PointerEvent): void {
    event.preventDefault();
    this.displayScreenshots = false;
    this.displayAdditions = false;
  }

  @HostListener('pointermove', ['$event'])
  onPointerMove(event: Event): void {
    event.preventDefault();
  }

  @HostListener('contextmenu')
  onContextMenu(): void {}

  captureStartEvent(event: AnimationEvent): void {
    if (event.fromState === 'void' && event.toState === null) {
      this.slideOpenInProgress = true;
    }
    if (event.fromState === null && event.toState === 'void') {
      this.slideCloseInProgress = true;
    }
    if (this.slideOpenInProgress && !this.slideCloseInProgress) {
      const height = this.element.nativeElement.scrollHeight;
      this.element.nativeElement.style.height = height + 'px';
      this.element.nativeElement.style.zIndex = 10;
    }
  }

  captureDoneEvent(event: AnimationEvent): void {
    if (event.fromState === 'void' && event.toState === null) {
      this.slideOpenInProgress = false;
    }
    if (event.fromState === null && event.toState === 'void') {
      if (!this.slideOpenInProgress && this.slideCloseInProgress) {
        this.element.nativeElement.style.height = 'auto';
        this.element.nativeElement.style.zIndex = 0;
      }
      this.slideCloseInProgress = false;
    }
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (changes['release']) {
      this.screenshotUrls = this.release.screenshots
        .map(s => s.imageUrl ?? s.fullImageUrl ?? '')
        .filter((url): url is string => url !== '');
    }
  }

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}play.svg`, 'play')?.subscribe();
  }

  get canPlay(): boolean {
    const type = this.release.emulatorType;
    return this.release.isPlayable
      && this.release.isDownloadable
      && this.release.playUrl !== null
      && type !== null
      && SUPPORTED_EMULATOR_TYPES.includes(type as EmulatorType);
  }

  onPlay(): void {
    const type = this.release.emulatorType as EmulatorType | null;
    if (!type || !this.release.playUrl) {
      return;
    }
    this.emulator.open({
      emulatorType: type,
      fileUrl: this.release.playUrl,
      uploadUrl: this.screenshotUploadUrl,
      canScreenshot: this.canUploadScreenshot,
    });
  }
}
