import {ParamMap, Params} from '@angular/router';

export type UsersListStatus = 'all' | 'active' | 'banned';
export type UsersListSort = 'dateCreated' | 'userName' | 'email';
export type UsersListDirection = 'asc' | 'desc';

/** What the management users list shows: kept in the URL, sent to `/users-data/`. */
export interface UsersListQuery {
  search: string;
  /** 0 for any group. */
  groupId: number;
  status: UsersListStatus;
  sort: UsersListSort;
  direction: UsersListDirection;
  page: number;
}

const STATUSES: readonly UsersListStatus[] = ['all', 'active', 'banned'];
const SORTS: readonly UsersListSort[] = ['dateCreated', 'userName', 'email'];
const DIRECTIONS: readonly UsersListDirection[] = ['asc', 'desc'];

/** The newest users first, nothing filtered out. */
export const DEFAULT_USERS_LIST_QUERY: Readonly<UsersListQuery> = {
  search: '',
  groupId: 0,
  status: 'all',
  sort: 'dateCreated',
  direction: 'desc',
  page: 1,
};

function pick<T extends string>(value: string | null, allowed: readonly T[], fallback: T): T {
  return allowed.find(item => item === value) ?? fallback;
}

function positiveNumber(value: string | null, fallback: number): number {
  const number = Number(value);
  return Number.isInteger(number) && number > 0 ? number : fallback;
}

/** A date reads best newest first; text columns from A. */
function initialDirection(sort: UsersListSort): UsersListDirection {
  return sort === 'dateCreated' ? 'desc' : 'asc';
}

export function usersListQueryFromParams(params: ParamMap): UsersListQuery {
  return {
    search: params.get('search') ?? '',
    groupId: positiveNumber(params.get('group'), 0),
    status: pick(params.get('status'), STATUSES, DEFAULT_USERS_LIST_QUERY.status),
    sort: pick(params.get('sort'), SORTS, DEFAULT_USERS_LIST_QUERY.sort),
    direction: pick(params.get('direction'), DIRECTIONS, DEFAULT_USERS_LIST_QUERY.direction),
    page: positiveNumber(params.get('page'), 1),
  };
}

/** The query params of the list URL: only what differs from the default, so the plain list keeps a plain URL. */
export function usersListQueryToParams(query: UsersListQuery): Params {
  const params: Params = {};
  if (query.search !== DEFAULT_USERS_LIST_QUERY.search) {
    params['search'] = query.search;
  }
  if (query.groupId !== DEFAULT_USERS_LIST_QUERY.groupId) {
    params['group'] = query.groupId;
  }
  if (query.status !== DEFAULT_USERS_LIST_QUERY.status) {
    params['status'] = query.status;
  }
  if (query.sort !== DEFAULT_USERS_LIST_QUERY.sort) {
    params['sort'] = query.sort;
  }
  if (query.direction !== DEFAULT_USERS_LIST_QUERY.direction) {
    params['direction'] = query.direction;
  }
  if (query.page !== DEFAULT_USERS_LIST_QUERY.page) {
    params['page'] = query.page;
  }
  return params;
}

/** The request params of `/users-data/`, every value spelled out. */
export function usersListRequestParams(query: UsersListQuery): Record<string, string | number> {
  return {
    search: query.search,
    groupId: query.groupId,
    status: query.status,
    sort: query.sort,
    direction: query.direction,
    page: query.page,
  };
}

/**
 * The query after a click on a column header: the same column flips its
 * direction, another one starts in its natural direction. Either way the list
 * starts again from its first page.
 */
export function sortUsersListBy(query: UsersListQuery, sort: UsersListSort): UsersListQuery {
  const direction = query.sort === sort
    ? (query.direction === 'asc' ? 'desc' : 'asc')
    : initialDirection(sort);
  return {...query, sort, direction, page: 1};
}
