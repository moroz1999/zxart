import {
  ChangeDetectionStrategy,
  Component,
  ElementRef,
  HostBinding,
  HostListener,
  Input,
  ViewChild,
} from '@angular/core';
import {FadeInOut} from '../../shared/animations/fade-in-out';
import {AnimationEvent, trigger} from '@angular/animations';
import {SlideInOut} from '../../shared/animations/slide-in-out';
import {ZxProdsListLayout} from '../zx-prods-category/zx-prods-category.component';
import {ZxProdComponent} from '../../shared/components/zx-prod-component';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../environments/environment';
import {TranslatePipe} from '@ngx-translate/core';
import {NgClass, NgForOf, NgIf} from '@angular/common';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../../shared/ui/zx-badge/zx-badge.component';
import {AnalyticsService} from '../../shared/services/analytics.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxItemControlsComponent} from '../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxReleaseTypeBadgeComponent} from '../../shared/ui/zx-release-type-badge/zx-release-type-badge.component';
import {ZxInsetComponent} from '../../shared/ui/zx-inset/zx-inset.component';
import {ZxInlineComponent} from '../../shared/ui/zx-inline/zx-inline.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxPartyPlaceComponent} from '../../shared/lib/zx-party-place/zx-party-place.component';
import {ZxCardScreenshotGalleryComponent} from '../../shared/ui/zx-card-screenshot-preview/zx-card-screenshot-gallery.component';

@Component({
  selector: 'zx-prod-block',
  templateUrl: './zx-prod-block.component.html',
  styleUrls: ['./zx-prod-block.component.scss'],
  animations: [
    trigger('fadeInOut', FadeInOut),
    trigger('slideInOut', SlideInOut),
  ],
  imports: [
    TranslatePipe,
    SvgIconComponent,
    NgIf,
    NgForOf,
    NgClass,
    ZxPanelComponent,
    ZxBadgeComponent,
    ZxButtonComponent,
    ZxItemControlsComponent,
    ZxReleaseTypeBadgeComponent,
    ZxInsetComponent,
    ZxInlineComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    ZxPartyPlaceComponent,
    ZxCardScreenshotGalleryComponent,
  ],
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdBlockComponent extends ZxProdComponent {
  @Input() imagesLayout: ZxProdsListLayout = 'loading';

  @HostBinding('class.inlays') get inlays(): boolean {
    return this.imagesLayout === 'inlays';
  }

  @ViewChild('inlaysGallery') private inlaysGallery?: ZxCardScreenshotGalleryComponent;

  displayScreenshots = false;
  displayAdditions = false;

  slideOpenInProgress = false;
  slideCloseInProgress = false;

  constructor(
    private element: ElementRef,
    private iconReg: SvgIconRegistryService,
    private analyticsService: AnalyticsService,
  ) {
    super();
    this.iconReg.loadSvg(`${environment.svgUrl}cart.svg`, 'cart')?.subscribe();
  }

  @HostListener('pointerenter', ['$event'])
  enterHandler(event: PointerEvent): void {
    event.preventDefault();
    this.displayAdditions = true;

    if (this.imagesLayout === 'inlays') {
      this.inlaysGallery?.reset();
    } else if (this.model.imagesUrls.length > 0) {
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

  captureStartEvent(event: AnimationEvent) {
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

  captureDoneEvent(event: AnimationEvent) {
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

  filterRoles(roles: string[]): string[] {
    return roles.filter(r => r !== 'unknown');
  }

  cartClicked(event: MouseEvent) {
    event.preventDefault();
    this.analyticsService.reachGoal('open-cart-link', {}, () => window.open(this.model.externalLink));
  }
}
