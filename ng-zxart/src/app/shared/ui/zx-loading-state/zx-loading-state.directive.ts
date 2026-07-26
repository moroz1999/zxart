import {GlobalPositionStrategy, Overlay, OverlayRef, ScrollDispatcher, ViewportRuler} from '@angular/cdk/overlay';
import {ComponentPortal} from '@angular/cdk/portal';
import {booleanAttribute, Directive, ElementRef, Input, OnChanges, OnDestroy} from '@angular/core';
import {merge, Subscription} from 'rxjs';
import {ZxSpinnerComponent} from '../zx-spinner/zx-spinner.component';

/**
 * Marks already displayed content as being refreshed: dims and blurs it, blocks interaction, and
 * shows a spinner over the part of it that is on screen.
 *
 * The spinner is attached to a CDK overlay instead of the host, because the blur filter applies to
 * every descendant and makes the host a containing block for fixed positioning.
 */
@Directive({
  selector: '[zxLoadingState]',
  standalone: true,
  host: {
    class: 'zx-loading-state',
    '[class.zx-loading-state--loading]': 'zxLoadingState',
    '[attr.aria-busy]': "zxLoadingState ? 'true' : null",
  },
})
export class ZxLoadingStateDirective implements OnChanges, OnDestroy {
  private static readonly spinnerClass = 'zx-loading-state-spinner';
  private static readonly visibleClass = 'zx-loading-state-spinner--visible';
  private static readonly trackThrottleMs = 50;

  @Input({transform: booleanAttribute}) zxLoadingState = false;

  private overlayRef?: OverlayRef;
  private positionStrategy?: GlobalPositionStrategy;
  private tracking?: Subscription;

  constructor(
    private readonly host: ElementRef<HTMLElement>,
    private readonly overlay: Overlay,
    private readonly scrollDispatcher: ScrollDispatcher,
    private readonly viewportRuler: ViewportRuler,
  ) {}

  ngOnChanges(): void {
    if (this.zxLoadingState) {
      this.showSpinner();
      return;
    }

    this.hideSpinner();
  }

  ngOnDestroy(): void {
    this.tracking?.unsubscribe();
    this.overlayRef?.dispose();
  }

  private showSpinner(): void {
    this.createSpinner();
    this.placeSpinner();
    this.tracking?.unsubscribe();
    // Both CDK streams emit outside the Angular zone; placing the spinner only touches the DOM.
    this.tracking = merge(
      this.scrollDispatcher.scrolled(ZxLoadingStateDirective.trackThrottleMs),
      this.viewportRuler.change(ZxLoadingStateDirective.trackThrottleMs),
    ).subscribe(() => this.placeSpinner());
  }

  private hideSpinner(): void {
    this.tracking?.unsubscribe();
    this.overlayRef?.removePanelClass(ZxLoadingStateDirective.visibleClass);
  }

  /**
   * The overlay is kept for the lifetime of the directive: the fade-out is a CSS transition on a
   * pane that stays transparent and click-through once hidden, so nothing has to wait for it.
   */
  private createSpinner(): void {
    if (this.overlayRef !== undefined) {
      return;
    }

    this.positionStrategy = this.overlay.position().global();
    this.overlayRef = this.overlay.create({
      positionStrategy: this.positionStrategy,
      scrollStrategy: this.overlay.scrollStrategies.noop(),
      panelClass: ZxLoadingStateDirective.spinnerClass,
    });
    this.overlayRef.attach(new ComponentPortal(ZxSpinnerComponent)).setInput('size', 'lg');
    // Flush the transparent starting style, otherwise the first fade-in is skipped entirely.
    this.overlayRef.overlayElement.getBoundingClientRect();
  }

  /** Centres the spinner on the intersection of the host and the viewport, or hides it when there is none. */
  private placeSpinner(): void {
    if (this.overlayRef === undefined || this.positionStrategy === undefined) {
      return;
    }

    const hostRect = this.host.nativeElement.getBoundingClientRect();
    const viewport = this.viewportRuler.getViewportSize();
    const left = Math.max(hostRect.left, 0);
    const right = Math.min(hostRect.right, viewport.width);
    const top = Math.max(hostRect.top, 0);
    const bottom = Math.min(hostRect.bottom, viewport.height);
    const onScreen = right > left && bottom > top;

    if (!onScreen) {
      this.overlayRef.removePanelClass(ZxLoadingStateDirective.visibleClass);
      return;
    }

    this.positionStrategy.left(`${(left + right) / 2}px`).top(`${(top + bottom) / 2}px`);
    this.overlayRef.updatePosition();
    this.overlayRef.addPanelClass(ZxLoadingStateDirective.visibleClass);
  }
}
