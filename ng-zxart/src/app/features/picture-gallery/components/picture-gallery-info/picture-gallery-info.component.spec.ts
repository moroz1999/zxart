import {CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {TestBed} from '@angular/core/testing';
import {CommonModule} from '@angular/common';
import {provideRouter, RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {DialogRef} from '@angular/cdk/dialog';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {PictureGalleryInfoComponent} from './picture-gallery-info.component';
import {PictureGalleryInfo} from '../../models/picture-gallery-info';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

const INFO: PictureGalleryInfo = {
  pictureId: 42,
  title: 'Capri Rendezvous',
  detailsUrl: '/picture/42',
  authors: [{name: 'Dimidrol', url: '/author/1'}],
  format: 'gigascreen',
  votes: 4.2,
  votesAmount: 17,
  userVote: null,
  denyVoting: false,
};

function render(info: PictureGalleryInfo) {
  const dialogRef = {close: vi.fn()};
  TestBed.configureTestingModule({
    imports: [PictureGalleryInfoComponent, TranslateModule.forRoot()],
    providers: [provideRouter([{path: '**', children: []}]), {provide: DialogRef, useValue: dialogRef}],
  });
  // the vote controls talk to the backend; only their presence matters here
  TestBed.overrideComponent(PictureGalleryInfoComponent, {
    set: {imports: [CommonModule, RouterLink, TranslateModule, TextDirective], schemas: [CUSTOM_ELEMENTS_SCHEMA]},
  });
  const fixture = TestBed.createComponent(PictureGalleryInfoComponent);
  fixture.componentRef.setInput('info', info);
  fixture.detectChanges();
  return {element: fixture.nativeElement as HTMLElement, dialogRef};
}

describe('PictureGalleryInfoComponent', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('names the picture, its authors and its format', () => {
    const {element} = render(INFO);

    const text = element.textContent ?? '';
    expect(text).toContain('Capri Rendezvous');
    expect(text).toContain('Dimidrol');
    expect(text).toContain('picture-format-short.gigascreen');
  });

  it('offers the vote controls of the picture', () => {
    const {element} = render(INFO);

    expect(element.querySelector('zx-item-controls')).not.toBeNull();
  });

  it('closes the gallery when a link leaves it', () => {
    const {element, dialogRef} = render(INFO);

    (element.querySelector('a') as HTMLAnchorElement).click();

    expect(dialogRef.close).toHaveBeenCalled();
  });
});
