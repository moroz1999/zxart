import {
  Component,
  ElementRef,
  Input,
  OnInit,
  Output,
  EventEmitter,
  HostBinding,
  OnChanges,
  SimpleChanges,
} from '@angular/core';
import {ZxProd} from '../zx-prods-list/models/zx-prod';
import {FadeInOut} from '../shared/animations/fade-in-out';
import {trigger, AnimationEvent} from '@angular/animations';
import {SlideInOut} from '../shared/animations/slide-in-out';

type ZxProdsListLayout = 'loading' | 'screenshots' | 'inlays' | 'table';

@Component({
  selector: 'app-zx-prod',
  templateUrl: './zx-prod.component.html',
  styleUrls: ['./zx-prod.component.scss'],
  animations: [
    trigger('fadeInOut', FadeInOut),
    trigger('slideInOut', SlideInOut),
  ],
})
export class ZxProdComponent implements OnInit, OnChanges {
  @Input() imagesLayout: ZxProdsListLayout = 'loading';
  @Input() model!: ZxProd;
  @Output() categoryChanged = new EventEmitter<number>();
  @Output() yearChanged = new EventEmitter<Array<string>>();
  @Output() hardwareChanged = new EventEmitter<Array<string>>();
  @Output() languageChanged = new EventEmitter<Array<string>>();

  @HostBinding('class.inlays') get inlays(): boolean {
    return this.imagesLayout === 'inlays';
  }

  displayScreenshots: boolean = false;
  displayAdditions: boolean = false;
  activeScreenshotUrl = '';

  slideOpenInProgress = false;
  slideCloseInProgress = false;

  constructor(private element: ElementRef) {
  }

  ngOnInit(): void {
    this.element.nativeElement.addEventListener('pointerenter', this.enterHandler.bind(this));
    this.element.nativeElement.addEventListener('pointerleave', this.leaveHandler.bind(this));
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes.imagesLayout) {
      if (this.imagesLayout !== 'inlays' && this.model.imagesUrls.length > 0) {
        this.activeScreenshotUrl = this.model.imagesUrls[0];
      } else if (this.imagesLayout === 'inlays' && this.model.inlaysUrls.length > 0) {
        this.activeScreenshotUrl = this.model.inlaysUrls[0];
      }
    }
  }

  enterHandler(): void {
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

  leaveHandler(): void {
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

  categoryClicked(event: Event, categoryId: number): void {
    event.preventDefault();
    this.categoryChanged.emit(categoryId);
  }

  hardwareClicked(event: Event, hardware: string): void {
    event.preventDefault();
    this.hardwareChanged.emit([hardware]);
  }


  yearClicked(event: Event, year: string): void {
    event.preventDefault();
    this.yearChanged.emit([year]);
  }

  languageClicked(event: Event, language: string): void {
    event.preventDefault();
    this.languageChanged.emit([language]);
  }
}
