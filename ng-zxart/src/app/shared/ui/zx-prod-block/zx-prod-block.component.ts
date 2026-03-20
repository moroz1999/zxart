import {
  ChangeDetectionStrategy,
  Component,
  ElementRef,
  HostBinding,
  HostListener,
  Input,
  OnChanges,
  SimpleChanges,
} from '@angular/core';
import {FadeInOut} from '../../animations/fade-in-out';
import {AnimationEvent, trigger} from '@angular/animations';
import {SlideInOut} from '../../animations/slide-in-out';
import {ZxProdsListLayout} from '../../../entities/zx-prods-category/zx-prods-category.component';
import {ZxProdComponent} from '../../components/zx-prod-component';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {TranslatePipe} from '@ngx-translate/core';
import {NgClass, NgForOf, NgIf} from '@angular/common';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {AnalyticsService} from '../../services/analytics.service';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxItemControlsComponent} from '../zx-item-controls/zx-item-controls.component';

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
  ],
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdBlockComponent extends ZxProdComponent implements OnChanges {
  @Input() imagesLayout: ZxProdsListLayout = 'loading';

  @HostBinding('class.inlays') get inlays(): boolean {
    return this.imagesLayout === 'inlays';
  }

  displayScreenshots = false;
  displayAdditions = false;
  activeScreenshotUrl = '';

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

  ngOnChanges(changes: SimpleChanges) {
    if (changes['imagesLayout']) {
      if (this.imagesLayout !== 'inlays' && this.model.imagesUrls.length > 0) {
        this.activeScreenshotUrl = this.model.imagesUrls[0];
      } else if (this.imagesLayout === 'inlays' && this.model.inlaysUrls.length > 0) {
        this.activeScreenshotUrl = this.model.inlaysUrls[0];
      } else {
        this.activeScreenshotUrl = '';
      }
    }
  }

  @HostListener('pointerenter', ['$event'])
  enterHandler(event: PointerEvent): void {
    event.preventDefault();
    this.displayAdditions = true;

    if (this.imagesLayout === 'inlays') {
      this.activeScreenshotUrl = this.model.inlaysUrls[0];
    } else {
      if (this.model.imagesUrls.length > 0) {
        this.displayScreenshots = true;
      }
      this.activeScreenshotUrl = this.model.imagesUrls[0];
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

  setActiveScreenshotUrl(imageUrl: string): void {
    this.activeScreenshotUrl = imageUrl;
  }

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

  cartClicked(event: MouseEvent) {
    event.preventDefault();
    this.analyticsService.reachGoal('open-cart-link', {}, () => window.open(this.model.externalLink));
  }
}
