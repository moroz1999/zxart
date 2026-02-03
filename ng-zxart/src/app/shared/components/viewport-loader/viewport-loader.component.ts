import {CommonModule} from '@angular/common';
import {Component, ContentChild, Input, OnInit, TemplateRef} from '@angular/core';
import {animate, style, transition, trigger} from '@angular/animations';
import {merge, Observable, of, Subject} from 'rxjs';
import {shareReplay, startWith, switchMap} from 'rxjs/operators';
import {InViewportDirective} from '../../directives/in-viewport.directive';

@Component({
  selector: 'app-viewport-loader',
  standalone: true,
  imports: [CommonModule, InViewportDirective],
  animations: [
    trigger('fade', [
      transition(':enter', [
        style({opacity: 0}),
        animate('150ms ease-in', style({opacity: 1}))
      ]),
      transition(':leave', [
        animate('150ms ease-out', style({opacity: 0}))
      ])
    ])
  ],
  template: `
    <div [appInViewport]="enabled" (inViewport)="onInViewport()">
      <ng-container *ngIf="dataStream$ | async as data; else skeletonWrapper">
        <div @fade>
          <ng-container *ngTemplateOutlet="contentRef; context: {$implicit: data}"></ng-container>
        </div>
      </ng-container>
    </div>

    <ng-template #skeletonWrapper>
      <div *ngIf="enabled" @fade>
        <ng-container *ngTemplateOutlet="skeletonTemplate"></ng-container>
      </div>
    </ng-template>
  `
})
export class ViewportLoaderComponent<T> implements OnInit {
  @Input() enabled: boolean = true;
  @Input() loader!: () => Observable<T>;
  @Input() reload$: Observable<void> | null = null;

  @ContentChild('skeleton', {read: TemplateRef}) skeletonTemplate!: TemplateRef<any>;
  @ContentChild('content', {read: TemplateRef}) contentRef!: TemplateRef<any>;

  private becameVisibleSubject = new Subject<void>();
  dataStream$: Observable<T | null> = of(null);

  ngOnInit(): void {
    if (!this.enabled) {
      return;
    }

    const triggers: Observable<void>[] = [this.becameVisibleSubject];
    if (this.reload$) {
      triggers.push(this.reload$);
    }

    this.dataStream$ = merge(...triggers).pipe(
      switchMap(() => this.loader()),
      startWith(null),
      shareReplay({bufferSize: 1, refCount: true})
    );
  }

  onInViewport(): void {
    if (this.enabled) {
      this.becameVisibleSubject.next();
    }
  }
}
