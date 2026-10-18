import {describe, expect, it} from 'vitest';
import {commentLink} from './comment-link';

describe('commentLink', () => {
  it('opens the discussion tab of a production', () => {
    expect(commentLink(601327, {type: 'zxProd', url: '/prod/151567'}))
      .toEqual({path: '/prod/151567/discussion', fragment: 'comment601327'});
  });

  it('opens the discussion tab of an author and a group', () => {
    expect(commentLink(1, {type: 'author', url: '/author/5'}).path).toBe('/author/5/discussion');
    expect(commentLink(1, {type: 'group', url: '/group/7'}).path).toBe('/group/7/discussion');
  });

  it('opens the activity tab of a party', () => {
    expect(commentLink(1, {type: 'party', url: '/party/9'}).path).toBe('/party/9/activity');
  });

  it('keeps the page of a work whose comments are not in a tab', () => {
    expect(commentLink(601212, {type: 'zxRelease', url: '/release/601194'}))
      .toEqual({path: '/release/601194', fragment: 'comment601212'});
  });

  it('replaces a tab segment the target URL already carries', () => {
    expect(commentLink(1, {type: 'zxProd', url: '/prod/151567/media'}).path).toBe('/prod/151567/discussion');
  });
});
