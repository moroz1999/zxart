import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {PictureGallerySlideComponent} from './picture-gallery-slide.component';
import {PictureGallerySlideData} from '../../services/picture-gallery.mapper';
import {PictureGalleryService} from '../../services/picture-gallery.service';
import {SvgIconRegistryService} from 'angular-svg-icon';

vi.mock('ng-gallery', () => ({ImageItem: class {}, Gallery: class {}}));
vi.mock('ng-gallery/lightbox', () => ({Lightbox: class {}}));

const SLIDE: PictureGallerySlideData = {
  src: '/zximages/id=1;zoom=1',
  zoomSrc: '/zximages/id=1;zoom=2',
  thumb: '/zximages/id=1;zoom=1',
  alt: 'Capri',
  info: {
    pictureId: 1,
    title: 'Capri',
    detailsUrl: '/picture/1',
    authors: [],
    format: 'standard',
    votes: 0,
    votesAmount: 0,
    userVote: null,
    denyVoting: false,
  },
};

const galleryService = {showNext: vi.fn(), showPrevious: vi.fn()};

function render(first = false, last = false, active = true) {
  TestBed.configureTestingModule({
    imports: [PictureGallerySlideComponent, TranslateModule.forRoot()],
    providers: [
      {provide: PictureGalleryService, useValue: galleryService},
      {provide: SvgIconRegistryService, useValue: {loadSvg: vi.fn()}},
    ],
  });
  // the info block and the zoom overlay are separate units; only their placement matters here
  TestBed.overrideComponent(PictureGallerySlideComponent, {
    set: {imports: [CommonModule, TranslateModule], schemas: [CUSTOM_ELEMENTS_SCHEMA]},
  });
  const fixture = TestBed.createComponent(PictureGallerySlideComponent);
  fixture.componentRef.setInput('slide', SLIDE);
  fixture.componentRef.setInput('active', active);
  fixture.componentRef.setInput('first', first);
  fixture.componentRef.setInput('last', last);
  fixture.detectChanges();
  return fixture;
}

function setSize(element: Element, width: number, height: number): void {
  Object.defineProperty(element, 'clientWidth', {configurable: true, value: width});
  Object.defineProperty(element, 'clientHeight', {configurable: true, value: height});
}

describe('PictureGallerySlideComponent', () => {
  beforeEach(() => {
    vi.stubGlobal('ResizeObserver', class {
      observe(): void {}
      disconnect(): void {}
    });
  });
  afterEach(() => {
    vi.unstubAllGlobals();
    TestBed.resetTestingModule();
  });

  it('shows the picture at the largest whole multiple of its native size that fits', () => {
    const fixture = render();
    const element = fixture.nativeElement as HTMLElement;
    setSize(element.querySelector('.pgs-canvas')!, 1000, 800);
    const image = element.querySelector('img') as HTMLImageElement;
    Object.defineProperty(image, 'naturalWidth', {value: 256});
    Object.defineProperty(image, 'naturalHeight', {value: 192});

    image.dispatchEvent(new Event('load'));
    fixture.detectChanges();

    expect(image.style.width).toBe('768px');
    expect(image.style.height).toBe('576px');
  });

  it('keeps the picture info in its own block beside the stage', () => {
    const element = render().nativeElement as HTMLElement;

    const info = element.querySelector('zx-picture-gallery-info');
    expect(info).not.toBeNull();
    expect(element.querySelector('.pgs-stage')!.contains(info)).toBe(false);
  });

  it('keeps the arrows inside the picture block, clear of the info', () => {
    const element = render().nativeElement as HTMLElement;

    const stage = element.querySelector('.pgs-stage')!;
    expect(stage.querySelector('.pgs-nav--prev')).not.toBeNull();
    expect(stage.querySelector('.pgs-nav--next')).not.toBeNull();
  });

  it('turns to the next and the previous picture', () => {
    const element = render().nativeElement as HTMLElement;

    (element.querySelector('.pgs-nav--next') as HTMLElement).click();
    (element.querySelector('.pgs-nav--prev') as HTMLElement).click();

    expect(galleryService.showNext).toHaveBeenCalled();
    expect(galleryService.showPrevious).toHaveBeenCalled();
  });

  it('offers no way back from the first picture and none further from the last', () => {
    const element = render(true, true).nativeElement as HTMLElement;

    expect(element.querySelector('.pgs-nav--prev')).toBeNull();
    expect(element.querySelector('.pgs-nav--next')).toBeNull();
  });

  it('takes the slides around the shown one out of focus, so opening the gallery never scrolls to them', () => {
    // the lightbox focuses its first focusable element on opening
    expect((render(false, false, false).nativeElement as HTMLElement).hasAttribute('inert')).toBe(true);
  });

  it('keeps the shown slide focusable', () => {
    expect((render().nativeElement as HTMLElement).hasAttribute('inert')).toBe(false);
  });
});
