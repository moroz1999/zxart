import {convertToParamMap} from '@angular/router';
import {describe, expect, it} from 'vitest';
import {
  DEFAULT_USERS_LIST_QUERY,
  sortUsersListBy,
  usersListQueryFromParams,
  usersListQueryToParams,
  usersListRequestParams,
} from './users-list-query';

describe('users list query', () => {
  it('starts with the newest users first and no filter', () => {
    expect(usersListQueryFromParams(convertToParamMap({}))).toEqual(DEFAULT_USERS_LIST_QUERY);
    expect(DEFAULT_USERS_LIST_QUERY).toEqual({
      search: '',
      groupId: 0,
      status: 'all',
      sort: 'dateCreated',
      direction: 'desc',
      page: 1,
    });
  });

  it('reads every filter from the URL', () => {
    const params = convertToParamMap({
      search: 'gold',
      group: '479',
      status: 'banned',
      sort: 'userName',
      direction: 'asc',
      page: '3',
    });

    expect(usersListQueryFromParams(params)).toEqual({
      search: 'gold',
      groupId: 479,
      status: 'banned',
      sort: 'userName',
      direction: 'asc',
      page: 3,
    });
  });

  it('falls back to the default for a value it does not know', () => {
    const params = convertToParamMap({group: 'x', status: 'gone', sort: 'password', direction: 'up', page: '-2'});

    expect(usersListQueryFromParams(params)).toEqual(DEFAULT_USERS_LIST_QUERY);
  });

  it('keeps only what differs from the default in the URL', () => {
    expect(usersListQueryToParams(DEFAULT_USERS_LIST_QUERY)).toEqual({});
    expect(usersListQueryToParams({...DEFAULT_USERS_LIST_QUERY, search: 'gold', groupId: 479, page: 2})).toEqual({
      search: 'gold',
      group: 479,
      page: 2,
    });
  });

  it('sends every value to the endpoint', () => {
    expect(usersListRequestParams({...DEFAULT_USERS_LIST_QUERY, search: 'gold'})).toEqual({
      search: 'gold',
      groupId: 0,
      status: 'all',
      sort: 'dateCreated',
      direction: 'desc',
      page: 1,
    });
  });

  it('flips the direction when the same column is asked again and goes back to the first page', () => {
    const query = {...DEFAULT_USERS_LIST_QUERY, page: 4};

    expect(sortUsersListBy(query, 'dateCreated')).toEqual({...DEFAULT_USERS_LIST_QUERY, direction: 'asc'});
  });

  it('sorts a new column ascending, a date descending', () => {
    expect(sortUsersListBy(DEFAULT_USERS_LIST_QUERY, 'email')).toEqual({
      ...DEFAULT_USERS_LIST_QUERY,
      sort: 'email',
      direction: 'asc',
    });
    expect(sortUsersListBy({...DEFAULT_USERS_LIST_QUERY, sort: 'email', direction: 'asc'}, 'dateCreated')).toEqual(
      DEFAULT_USERS_LIST_QUERY,
    );
  });
});
