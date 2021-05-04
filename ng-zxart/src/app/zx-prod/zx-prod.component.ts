import {Component, ElementRef, Input, OnInit} from '@angular/core';
import {ZxProd} from '../zx-prods-list/models/zx-prod';

@Component({
  selector: 'app-zx-prod',
  templateUrl: './zx-prod.component.html',
  styleUrls: ['./zx-prod.component.scss'],
})
export class ZxProdComponent implements OnInit {
  @Input() model!: ZxProd;
  displayScreenshots: boolean = false;
  displayAdditions: boolean = false;
  activeScreenshotUrl = '';

  constructor(private element: ElementRef) {
  }

  ngOnInit(): void {
    this.element.nativeElement.addEventListener('pointerenter', this.enterHandler.bind(this))
    this.element.nativeElement.addEventListener('pointerleave', this.leaveHandler.bind(this))
  }

  enterHandler(): void {
    this.displayScreenshots = true;
    this.displayAdditions = true;
    this.activeScreenshotUrl = this.model.imagesUrls[0];
  }

  leaveHandler(): void {
    this.displayScreenshots = false;
    this.displayAdditions = false;
  }

  setActiveScreenshotUrl(imageUrl: string): void {
    this.activeScreenshotUrl = imageUrl;
  }
}
