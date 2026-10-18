import {TestBed} from '@angular/core/testing';
import {TranslateModule} from '@ngx-translate/core';
import {afterEach, describe, expect, it} from 'vitest';
import {ZxTuneDownloadsPanelComponent} from './zx-tune-downloads-panel.component';
import {TuneDownloadDto} from '../../models/tune-details.dto';

function render(downloads: TuneDownloadDto[]): HTMLElement {
  TestBed.configureTestingModule({
    imports: [ZxTuneDownloadsPanelComponent, TranslateModule.forRoot()],
  });
  const fixture = TestBed.createComponent(ZxTuneDownloadsPanelComponent);
  fixture.componentRef.setInput('downloads', downloads);
  fixture.detectChanges();
  return fixture.nativeElement as HTMLElement;
}

describe('ZxTuneDownloadsPanelComponent', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('labels the tracker file and the executable apart, under their file names', () => {
    const element = render([
      {id: 'tracker', kind: 'tracker', ext: 'vt2', fileName: 'Song.vt2', size: null, url: '/t'},
      {id: 'executable', kind: 'executable', ext: 'tap', fileName: 'Song.tap', size: null, url: '/e'},
    ]);

    const text = element.textContent ?? '';
    expect(text).toContain('tune-details.download-kind.tracker');
    expect(text).toContain('tune-details.download-kind.executable');
    expect(text).toContain('Song.tap');
  });
});
