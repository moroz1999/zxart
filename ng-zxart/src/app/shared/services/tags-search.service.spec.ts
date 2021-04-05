import { TestBed } from '@angular/core/testing';

import { TagsSearchService } from './tags-search.service';

describe('TagsSearchService', () => {
  let service: TagsSearchService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TagsSearchService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
