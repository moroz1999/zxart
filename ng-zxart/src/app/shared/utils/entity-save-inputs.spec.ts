import {describe, expect, it} from 'vitest';
import {authorshipInputs, dateToTimestamp, importOriginInputs, membershipInputs} from './entity-save-inputs';

describe('entity save inputs', () => {
  it('reads a d.m.Y date as midnight UTC, the way the server stores it', () => {
    expect(dateToTimestamp('01.01.1991')).toBe(662688000);
    expect(dateToTimestamp(' 5.3.1999 ')).toBe(920592000);
  });

  it('reads an empty or malformed date as no date', () => {
    expect(dateToTimestamp('')).toBe(0);
    expect(dateToTimestamp('sometime')).toBe(0);
    expect(dateToTimestamp('31.02.1999')).toBe(0);
  });

  it('turns the member editor state into memberships, one per member', () => {
    const fields = {
      roles: {'10': ['coder'], '20': []},
      startDates: {'10': '01.01.1991'},
      endDates: {'10': '', '20': ''},
    };

    expect(membershipInputs(fields)).toEqual([
      {elementId: 10, roles: ['coder'], startDate: 662688000, endDate: 0},
      {elementId: 20, roles: [], startDate: 0, endDate: 0},
    ]);
  });

  it('turns the member editor state into authorships', () => {
    const fields = {roles: {'4': ['music']}, startDates: {}, endDates: {'4': '05.03.1999'}};

    expect(authorshipInputs(fields)).toEqual([{authorId: 4, roles: ['music'], startDate: 0, endDate: 920592000}]);
  });

  it('keeps the portal ids that have an id', () => {
    const fields = {
      '0': {origin: 'zxdb', importId: ' 9001 '},
      '1': {origin: 'vt', importId: ''},
    };

    expect(importOriginInputs(fields)).toEqual([{origin: 'zxdb', importId: '9001'}]);
  });
});
