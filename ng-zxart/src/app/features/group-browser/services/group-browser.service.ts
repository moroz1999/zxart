import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PaginatedGroupsResponse} from '../models/group-list-item';
import {GroupFilterOptions} from '../models/group-filter-options';

@Injectable({
  providedIn: 'root',
})
export class GroupBrowserService {
  constructor(private http: HttpClient) {}

  getPaged(
    elementId: number,
    start: number,
    limit: number,
    sorting: string,
    search: string,
    countryId: number | null,
    cityId: number | null,
    letter: string = '',
    types: string = '',
    groupType: string = '',
  ): Observable<PaginatedGroupsResponse> {
    const params: Record<string, string> = {
      elementId: String(elementId),
      start: String(start),
      limit: String(limit),
      sorting,
    };
    if (search) {
      params['search'] = search;
    }
    if (countryId !== null) {
      params['countryId'] = String(countryId);
    }
    if (cityId !== null) {
      params['cityId'] = String(cityId);
    }
    if (letter) {
      params['letter'] = letter;
    }
    if (types) {
      params['types'] = types;
    }
    if (groupType) {
      params['groupType'] = groupType;
    }

    return this.http.get<PaginatedGroupsResponse>('/grouplist/', {params}).pipe(
      catchError(() => of({total: 0, items: []})),
    );
  }

  getFilterOptions(elementId: number, letter: string = '', groupType: string = ''): Observable<GroupFilterOptions> {
    const params: Record<string, string> = {action: 'filters', elementId: String(elementId)};
    if (letter) {
      params['letter'] = letter;
    }
    if (groupType) {
      params['groupType'] = groupType;
    }
    return this.http.get<GroupFilterOptions>('/grouplist/', {params}).pipe(
      catchError(() => of({countries: [], cities: []})),
    );
  }
}
