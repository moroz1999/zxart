import {ComponentRef, Directive, HostListener, Input, OnDestroy} from '@angular/core';
import {GlobalPositionStrategy, Overlay, OverlayRef} from '@angular/cdk/overlay';
import {ComponentPortal} from '@angular/cdk/portal';
import {TooltipOverlayComponent} from './tooltip-overlay.component';

/**
 * `[zxTooltip]` — floating tooltip that follows the cursor.
 *
 * Uses Angular CDK `Overlay` with `GlobalPositionStrategy` to render the tooltip
 * inside the CDK overlay container. The tooltip is positioned at cursor coordinates
 * and updates on every `mousemove`.
 *
 * The overlay component is attached to the DOM on `mouseenter` and detached after
 * the hide animation completes on `mouseleave` — leaving no leftover DOM nodes.
 *
 * ## Usage
 *
 * ```html
 * <div [zxTooltip]="'Some text'">Hover me</div>
 * <div [zxTooltip]="dynamicText">Dynamic text</div>
 * ```
 *
 * Empty string disables the tooltip.
 *
 * ## Styling
 *
 * Visual styles are defined via `.zx-tooltip` in `_zx-tooltip.theme.scss`.
 * The CDK overlay handles positioning and z-index stacking.
 *
 * Docs: docs/design-system/zx-tooltip.md
 */
@Directive({
  selector: '[zxTooltip]',
  standalone: true,
})
export class TooltipDirective implements OnDestroy {
  /** Tooltip text. Empty string disables the tooltip. */
  @Input('zxTooltip') text: string = '';

  private overlayRef: OverlayRef | null = null;
  private positionStrategy: GlobalPositionStrategy | null = null;
  private componentRef: ComponentRef<TooltipOverlayComponent> | null = null;
  private transitionEndHandler: (() => void) | null = null;
  private readonly OFFSET = 12;

  constructor(private overlay: Overlay) {}

  @HostListener('mouseenter', ['$event'])
  onMouseEnter(event: MouseEvent): void {
    if (!this.text) return;

    this.cancelTransitionEnd();
    this.ensureOverlay();

    if (!this.overlayRef!.hasAttached()) {
      this.componentRef = this.overlayRef!.attach(new ComponentPortal(TooltipOverlayComponent));
    }

    this.componentRef!.instance.text = this.text;
    this.updatePosition(event);

    requestAnimationFrame(() => {
      if (this.componentRef) {
        this.componentRef.instance.visible = true;
        this.componentRef.changeDetectorRef.detectChanges();
      }
    });
  }

  @HostListener('mousemove', ['$event'])
  onMouseMove(event: MouseEvent): void {
    this.updatePosition(event);
  }

  @HostListener('mouseleave')
  onMouseLeave(): void {
    if (!this.componentRef) return;
    this.componentRef.instance.visible = false;
    this.componentRef.changeDetectorRef.detectChanges();

    const el = this.overlayRef!.overlayElement.firstElementChild as HTMLElement;
    this.transitionEndHandler = () => {
      this.cancelTransitionEnd();
      this.overlayRef?.detach();
      this.componentRef = null;
    };
    el.addEventListener('transitionend', this.transitionEndHandler, {once: true});
  }

  private cancelTransitionEnd(): void {
    if (!this.transitionEndHandler) return;
    const el = this.overlayRef?.overlayElement.firstElementChild as HTMLElement | null;
    el?.removeEventListener('transitionend', this.transitionEndHandler);
    this.transitionEndHandler = null;
  }

  private ensureOverlay(): void {
    if (this.overlayRef) return;
    this.positionStrategy = this.overlay.position().global().left('0px').top('0px');
    this.overlayRef = this.overlay.create({
      positionStrategy: this.positionStrategy,
      hasBackdrop: false,
    });
  }

  private updatePosition(event: MouseEvent): void {
    if (!this.overlayRef || !this.positionStrategy) return;
    const offset = this.OFFSET;
    const el = this.overlayRef.overlayElement;
    let x = event.clientX + offset;
    let y = event.clientY + offset;

    if (x + el.offsetWidth > window.innerWidth) {
      x = event.clientX - el.offsetWidth - offset;
    }
    if (y + el.offsetHeight > window.innerHeight) {
      y = event.clientY - el.offsetHeight - offset;
    }

    this.positionStrategy.left(`${x}px`).top(`${y}px`);
    this.overlayRef.updatePosition();
  }

  ngOnDestroy(): void {
    this.cancelTransitionEnd();
    this.overlayRef?.dispose();
    this.overlayRef = null;
    this.positionStrategy = null;
    this.componentRef = null;
  }
}
