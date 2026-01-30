import {Directive, ElementRef, EventEmitter, Input, NgZone, OnDestroy, OnInit, Output} from '@angular/core';

@Directive({
  selector: '[appInViewport]',
  standalone: true
})
export class InViewportDirective implements OnDestroy, OnInit {
  @Input() appInViewport: boolean = true;
  @Output() inViewport = new EventEmitter<void>();
  private observer: IntersectionObserver | null = null;

  constructor(
    private el: ElementRef,
    private ngZone: NgZone
  ) {
  }

  ngOnInit(): void {
    if (this.appInViewport) {
      this.initObserver();
    }
  }

  private initObserver(): void {
    this.ngZone.runOutsideAngular(() => {
      this.observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
          this.ngZone.run(() => {
            this.inViewport.emit();
            this.disconnect();
          });
        }
      });

      this.observer.observe(this.el.nativeElement);
    });
  }

  private disconnect(): void {
    if (this.observer) {
      this.observer.disconnect();
      this.observer = null;
    }
  }

  ngOnDestroy(): void {
    this.disconnect();
  }
}
