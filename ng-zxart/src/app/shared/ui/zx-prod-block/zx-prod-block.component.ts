import {
  ChangeDetectorRef,
  Component,
  ElementRef,
  HostBinding,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import {FadeInOut} from '../../animations/fade-in-out';
import {AnimationEvent, trigger} from '@angular/animations';
import {SlideInOut} from '../../animations/slide-in-out';
import {ZxProdsListLayout} from '../../../entities/zx-prods-category/zx-prods-category.component';
import {ZxProdComponent} from '../../components/zx-prod-component';
import {VoteService} from '../../services/vote.service';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {TranslatePipe} from '@ngx-translate/core';
import {RatingComponent} from '../../components/rating/rating.component';
import {NgClass, NgForOf, NgIf} from '@angular/common';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {AnalyticsService} from '../../services/analytics.service';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxPlaylistButtonComponent} from '../zx-playlist-button/zx-playlist-button.component';

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
    RatingComponent,
    SvgIconComponent,
    NgIf,
    NgForOf,
    NgClass,
    ZxPanelComponent,
    ZxBadgeComponent,
    ZxButtonComponent,
    ZxPlaylistButtonComponent,
  ],
  standalone: true,
})
export class ZxProdBlockComponent extends ZxProdComponent implements OnInit, OnChanges {
  @Input() imagesLayout: ZxProdsListLayout = 'loading';

  @HostBinding('class.inlays') get inlays(): boolean {
    return this.imagesLayout === 'inlays';
  }

  displayScreenshots: boolean = false;
  displayAdditions: boolean = false;
  activeScreenshotUrl = '';

  slideOpenInProgress = false;
  slideCloseInProgress = false;

  constructor(
    private cdr: ChangeDetectorRef,
    private element: ElementRef,
    private voting: VoteService,
    private iconReg: SvgIconRegistryService,
    private analyticsService: AnalyticsService,
  ) {
    super();
  }

  ngOnInit(): void {
    this.element.nativeElement.addEventListener('pointerenter', this.enterHandler.bind(this));
    this.element.nativeElement.addEventListener('pointerleave', this.leaveHandler.bind(this));
    this.element.nativeElement.addEventListener('pointermove', (event: Event) => event.preventDefault());
    this.element.nativeElement.addEventListener('contextmenu', (event: Event) => {
    });
    this.iconReg.loadSvg(`${environment.svgUrl}cart.svg`, 'cart')?.subscribe();
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes.imagesLayout) {
      if (this.imagesLayout !== 'inlays' && this.model.imagesUrls.length > 0) {
        this.activeScreenshotUrl = this.model.imagesUrls[0];
      } else if (this.imagesLayout === 'inlays' && this.model.inlaysUrls.length > 0) {
        this.activeScreenshotUrl = this.model.inlaysUrls[0];
      } else {
        this.activeScreenshotUrl = '';
      }
    }
  }

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

  leaveHandler(event: PointerEvent): void {
    event.preventDefault();

    this.displayScreenshots = false;
    this.displayAdditions = false;
  }

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
      let height = this.element.nativeElement.scrollHeight;
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

  vote(rating: number) {
    this.voting.send<'zxProd'>(this.model.id, rating, 'zxProd').subscribe(value => {
      this.model.votes = value;
      this.model.userVote = rating;
      this.cdr.detectChanges();
    });
  }

  cartClicked(event: MouseEvent) {
    event.preventDefault();
    this.analyticsService.reachGoal('open-cart-link', {}, () => window.open(this.model.externalLink));
  }
}
