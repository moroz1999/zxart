import {TestBed} from '@angular/core/testing';
import {TranslateModule} from '@ngx-translate/core';
import {afterEach, describe, expect, it} from 'vitest';
import {ZxPictureDownloadsPanelComponent} from './zx-picture-downloads-panel.component';
import {PictureDownloadDto} from '../../models/picture-details.dto';

function render(downloads: PictureDownloadDto[]): HTMLElement {
  TestBed.configureTestingModule({
    imports: [ZxPictureDownloadsPanelComponent, TranslateModule.forRoot()],
  });
  const fixture = TestBed.createComponent(ZxPictureDownloadsPanelComponent);
  fixture.componentRef.setInput('downloads', downloads);
  fixture.detectChanges();
  return fixture.nativeElement as HTMLElement;
}

function download(kind: PictureDownloadDto['kind'], extra: Partial<PictureDownloadDto> = {}): PictureDownloadDto {
  return {id: kind, kind, ext: 'zip', zoom: null, fileName: null, size: null, url: `/file/${kind}`, ...extra};
}

describe('ZxPictureDownloadsPanelComponent', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('labels each file by its kind', () => {
    const element = render([download('stages'), download('executable')]);

    const text = element.textContent ?? '';
    expect(text).toContain('picture-details.download-kind.stages');
    expect(text).toContain('picture-details.download-kind.executable');
  });

  it('names a material by its file and a render by its zoom', () => {
    const element = render([
      download('stages', {fileName: 'capri.gif'}),
      download('pc', {ext: 'png', zoom: 2}),
    ]);

    const text = element.textContent ?? '';
    expect(text).toContain('capri.gif');
    expect(text).toContain('2×');
  });
});
