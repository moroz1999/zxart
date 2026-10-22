import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {PictureGallerySlideComponent} from './picture-gallery-slide.component';
import {PictureGallerySlide} from '../../models/picture-gallery-slide';

const SLIDE: PictureGallerySlide = {
  src: '/zximages/id=1;zoom=1',
  zoomSrc: '/zximages/id=1;zoom=2',
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

/** Native size every preloaded image reports. */
let nativeSize = {width: 256, height: 192};
/** The slide's resize callback, run once the test has sized its canvas. */
let resize: () => void = () => {};

function render(slide: PictureGallerySlide = SLIDE, hasPrevious = true, hasNext = true) {
  TestBed.configureTestingModule({
    imports: [PictureGallerySlideComponent, TranslateModule.forRoot()],
    providers: [{provide: SvgIconRegistryService, useValue: {loadSvg: vi.fn()}}],
  });
  // the info block and the magnifier are units of their own; only their placement and inputs matter here
  TestBed.overrideComponent(PictureGallerySlideComponent, {
    set: {imports: [CommonModule, TranslateModule], schemas: [CUSTOM_ELEMENTS_SCHEMA]},
  });
  const fixture = TestBed.createComponent(PictureGallerySlideComponent);
  fixture.componentRef.setInput('slide', slide);
  fixture.componentRef.setInput('hasPrevious', hasPrevious);
  fixture.componentRef.setInput('hasNext', hasNext);
  fixture.detectChanges();
  const canvas = fixture.nativeElement.querySelector('.pgs-canvas') as HTMLElement;
  Object.defineProperty(canvas, 'clientWidth', {configurable: true, value: 1000});
  Object.defineProperty(canvas, 'clientHeight', {configurable: true, value: 800});
  resize();
  fixture.detectChanges();
  return fixture;
}

describe('PictureGallerySlideComponent', () => {
  beforeEach(() => {
    nativeSize = {width: 256, height: 192};
    vi.stubGlobal('ResizeObserver', class {
      constructor(callback: () => void) {
        resize = callback;
      }
      observe(): void {}
      disconnect(): void {}
    });
    vi.stubGlobal('Image', class {
      naturalWidth = 0;
      naturalHeight = 0;
      onload: (() => void) | null = null;
      set src(_value: string) {
        this.naturalWidth = nativeSize.width;
        this.naturalHeight = nativeSize.height;
        this.onload?.();
      }
    });
  });
  afterEach(() => {
    vi.unstubAllGlobals();
    TestBed.resetTestingModule();
  });

  it('shows the picture at the largest whole multiple of its native size that fits', () => {
    const fixture = render();

    const zoom = fixture.nativeElement.querySelector('zx-pixel-zoom');
    expect(zoom.width).toBe(768);
    expect(zoom.height).toBe(576);
  });

  it('keeps the picture info in its own block beside the picture', () => {
    const element = render().nativeElement as HTMLElement;

    const info = element.querySelector('zx-picture-gallery-info');
    expect(info).not.toBeNull();
    expect(element.querySelector('.pgs-stage')!.contains(info)).toBe(false);
  });

  it('shows no info block for an image without info', () => {
    const element = render({...SLIDE, info: null}).nativeElement as HTMLElement;

    expect(element.querySelector('zx-picture-gallery-info')).toBeNull();
  });

  it('keeps the arrows inside the picture block and reports them', () => {
    const fixture = render();
    const previous = vi.fn();
    const next = vi.fn();
    fixture.componentInstance.previous.subscribe(previous);
    fixture.componentInstance.next.subscribe(next);
    const stage = fixture.nativeElement.querySelector('.pgs-stage') as HTMLElement;

    (stage.querySelector('.pgs-nav--prev') as HTMLElement).click();
    (stage.querySelector('.pgs-nav--next') as HTMLElement).click();

    expect(previous).toHaveBeenCalled();
    expect(next).toHaveBeenCalled();
  });

  it('offers no way back from the first picture and none further from the last', () => {
    const element = render(SLIDE, false, false).nativeElement as HTMLElement;

    expect(element.querySelector('.pgs-nav--prev')).toBeNull();
    expect(element.querySelector('.pgs-nav--next')).toBeNull();
  });
});
