import {Component, ElementRef, Input, OnInit} from '@angular/core';
import {ZxProd} from '../zx-prods-list/models/zx-prod';
import {FadeInOut} from '../shared/animations/fade-in-out';
import {trigger} from '@angular/animations';
import {SlideInOut} from '../shared/animations/slide-in-out';

@Component({
  selector: 'app-zx-prod',
  templateUrl: './zx-prod.component.html',
  styleUrls: ['./zx-prod.component.scss'],
  animations: [
    trigger('fadeInOut', FadeInOut),
    trigger('slideInOut', SlideInOut),
  ],
})
export class ZxProdComponent implements OnInit {
  @Input() model!: ZxProd;
  displayScreenshots: boolean = false;
  displayAdditions: boolean = false;
  activeScreenshotUrl = '';

  constructor(private element: ElementRef) {
  }

  ngOnInit(): void {
    this.element.nativeElement.addEventListener('pointerenter', this.enterHandler.bind(this));
    this.element.nativeElement.addEventListener('pointerleave', this.leaveHandler.bind(this));
  }

  enterHandler(): void {
    this.displayScreenshots = true;
    this.displayAdditions = true;
    this.activeScreenshotUrl = this.model.imagesUrls[0];

    let height = this.element.nativeElement.scrollHeight;
    this.element.nativeElement.style.height = height + 'px';
    this.element.nativeElement.style.zIndex = 10;
  }

  leaveHandler(): void {
    this.displayScreenshots = false;
    this.displayAdditions = false;
    this.element.nativeElement.style.zIndex = 0;
  }

  setActiveScreenshotUrl(imageUrl: string): void {
    this.activeScreenshotUrl = imageUrl;
  }
}
