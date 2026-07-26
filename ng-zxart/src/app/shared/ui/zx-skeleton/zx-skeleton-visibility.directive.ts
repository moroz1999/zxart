import {Directive, ElementRef, OnDestroy, Renderer2, afterNextRender} from '@angular/core';

@Directive({
  selector: '[zxSkeletonVisibility]',
  standalone: true,
})
export class ZxSkeletonVisibilityDirective implements OnDestroy {
  private static readonly elements = new Map<Element, ZxSkeletonVisibilityDirective>();
  private static observer: IntersectionObserver | undefined;

  private observing = false;

  constructor(
    private readonly elementRef: ElementRef<HTMLElement>,
    private readonly renderer: Renderer2,
  ) {
    afterNextRender(() => this.observe());
  }

  ngOnDestroy(): void {
    if (!this.observing) {
      return;
    }

    ZxSkeletonVisibilityDirective.observer?.unobserve(this.elementRef.nativeElement);
    ZxSkeletonVisibilityDirective.elements.delete(this.elementRef.nativeElement);
  }

  private observe(): void {
    const element = this.elementRef.nativeElement;

    this.renderer.setStyle(element, 'visibility', 'hidden');
    ZxSkeletonVisibilityDirective.elements.set(element, this);
    ZxSkeletonVisibilityDirective.getObserver().observe(element);
    this.observing = true;
  }

  private setVisible(visible: boolean): void {
    this.renderer.setStyle(this.elementRef.nativeElement, 'visibility', visible ? 'visible' : 'hidden');
  }

  private static getObserver(): IntersectionObserver {
    if (!this.observer) {
      this.observer = new IntersectionObserver(entries => {
        for (const entry of entries) {
          this.elements.get(entry.target)?.setVisible(entry.isIntersecting);
        }
      });
    }

    return this.observer;
  }
}
