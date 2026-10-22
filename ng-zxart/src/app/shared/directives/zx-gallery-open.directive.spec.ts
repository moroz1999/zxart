import {Component} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {ZxGalleryOpenDirective} from './zx-gallery-open.directive';
import {GALLERY_OPENER} from '../models/gallery-opener';

@Component({
  standalone: true,
  imports: [ZxGalleryOpenDirective],
  template: '<img [zxGalleryOpen]="3" zxGalleryId="pictures-1" alt="">',
})
class HostComponent {}

describe('ZxGalleryOpenDirective', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('opens the named gallery at the given picture on click', () => {
    const opener = {open: vi.fn()};
    TestBed.configureTestingModule({
      imports: [HostComponent],
      providers: [{provide: GALLERY_OPENER, useValue: opener}],
    });
    const fixture = TestBed.createComponent(HostComponent);
    fixture.detectChanges();

    (fixture.nativeElement.querySelector('img') as HTMLElement).click();

    expect(opener.open).toHaveBeenCalledWith('pictures-1', 3);
  });
});
