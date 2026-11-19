import {TestBed} from '@angular/core/testing';
import {ActivatedRoute, convertToParamMap, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {of} from 'rxjs';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {ZxAuthorBrowserComponent} from './zx-author-browser.component';
import {AuthorBrowserService} from '../../services/author-browser.service';

describe('ZxAuthorBrowserComponent', () => {
  afterEach(() => TestBed.resetTestingModule());

  it('loads the selected letter and reloads when the letter changes', () => {
    const authorBrowserService = {
      getPaged: vi.fn(() => of({total: 0, items: []})),
      getFilterOptions: vi.fn(() => of({countries: [], cities: []})),
    };
    const emptyParams = convertToParamMap({});

    TestBed.configureTestingModule({
      imports: [ZxAuthorBrowserComponent],
      providers: [
        {provide: AuthorBrowserService, useValue: authorBrowserService},
        {provide: ActivatedRoute, useValue: {paramMap: of(emptyParams), queryParamMap: of(emptyParams)}},
        {provide: Router, useValue: {navigate: vi.fn()}},
        {provide: TranslateService, useValue: {currentLang: 'en'}},
      ],
    });
    TestBed.overrideComponent(ZxAuthorBrowserComponent, {set: {template: ''}});

    const fixture = TestBed.createComponent(ZxAuthorBrowserComponent);
    fixture.componentRef.setInput('letter', 'a');
    fixture.detectChanges();

    expect(authorBrowserService.getPaged).toHaveBeenLastCalledWith(
      0,
      0,
      50,
      'title,asc',
      '',
      null,
      null,
      'a',
      '',
      '',
    );
    expect(authorBrowserService.getFilterOptions).toHaveBeenLastCalledWith(0, 'a', '');

    fixture.componentRef.setInput('letter', 'b');
    fixture.detectChanges();

    expect(authorBrowserService.getPaged).toHaveBeenCalledTimes(2);
    expect(authorBrowserService.getPaged.mock.calls[1][7]).toBe('b');
    expect(authorBrowserService.getFilterOptions).toHaveBeenCalledTimes(2);
    expect(authorBrowserService.getFilterOptions.mock.calls[1][1]).toBe('b');
  });
});
