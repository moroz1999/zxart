import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {CommonModule} from '@angular/common';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {PictureGalleryDialogComponent} from './picture-gallery-dialog.component';
import {PictureGalleryItem} from '../../models/picture-gallery-item';

const ITEMS: PictureGalleryItem[] = [0, 1, 2].map(id => ({
  id,
  title: `Picture ${id}`,
  thumbUrl: `/thumb-${id}.png`,
  largeUrl: `/large-${id}.png`,
  detailsUrl: '',
}));

function render(startIndex: number) {
  // jsdom lays nothing out
  Element.prototype.scrollIntoView = vi.fn();
  const dialogRef = {close: vi.fn()};
  TestBed.configureTestingModule({
    imports: [PictureGalleryDialogComponent, TranslateModule.forRoot()],
    providers: [
      {provide: DIALOG_DATA, useValue: {items: ITEMS, startIndex}},
      {provide: DialogRef, useValue: dialogRef},
      {provide: SvgIconRegistryService, useValue: {loadSvg: vi.fn()}},
    ],
  });
  // the slide is a unit of its own; here only which picture it gets matters
  TestBed.overrideComponent(PictureGalleryDialogComponent, {
    set: {imports: [CommonModule, TranslateModule], schemas: [CUSTOM_ELEMENTS_SCHEMA]},
  });
  const fixture = TestBed.createComponent(PictureGalleryDialogComponent);
  fixture.detectChanges();
  return {fixture, component: fixture.componentInstance, dialogRef};
}

describe('PictureGalleryDialogComponent', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('opens on the picture it was asked for', () => {
    const {component} = render(2);

    expect(component.index()).toBe(2);
    expect(component.slide().src).toBe('/large-2.png');
  });

  it('turns pictures with the arrow keys and stops at the ends', () => {
    const {component} = render(1);

    document.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowRight'}));
    expect(component.index()).toBe(2);
    document.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowRight'}));
    expect(component.index()).toBe(2);
    document.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowLeft'}));
    expect(component.index()).toBe(1);
  });

  it('opens a picture picked from the thumbnails', () => {
    const {fixture, component} = render(0);

    (fixture.nativeElement.querySelectorAll('.pgd-thumb')[2] as HTMLElement).click();

    expect(component.index()).toBe(2);
  });

  it('turns to the next picture on a swipe to the left', () => {
    const {fixture, component} = render(0);
    const stage = fixture.nativeElement.querySelector('.pgd-slide') as HTMLElement;

    stage.dispatchEvent(Object.assign(new Event('pointerdown'), {pointerType: 'touch', clientX: 300, clientY: 100}));
    stage.dispatchEvent(Object.assign(new Event('pointerup'), {pointerType: 'touch', clientX: 120, clientY: 110}));

    expect(component.index()).toBe(1);
  });

  it('closes on the close button', () => {
    const {fixture, dialogRef} = render(0);

    (fixture.nativeElement.querySelector('.pgd-close') as HTMLElement).click();

    expect(dialogRef.close).toHaveBeenCalled();
  });
});
