import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PaginatedAuthorsResponse} from '../models/author-list-item';
import {AuthorFilterOptions} from '../models/author-filter-options';

@Injectable({
  providedIn: 'root',
})
export class AuthorBrowserService {
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
    items: string = '',
  ): Observable<PaginatedAuthorsResponse> {
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
    if (items) {
      params['items'] = items;
    }

    return this.http.get<PaginatedAuthorsResponse>('/authorlist/', {params}).pipe(
      catchError(() => of({total: 0, items: []})),
    );
  }

  getFilterOptions(elementId: number, letter: string = '', items: string = ''): Observable<AuthorFilterOptions> {
    const params: Record<string, string> = {action: 'filters', elementId: String(elementId)};
    if (letter) {
      params['letter'] = letter;
    }
    if (items) {
      params['items'] = items;
    }
    return this.http.get<AuthorFilterOptions>('/authorlist/', {params}).pipe(
      catchError(() => of({countries: [], cities: []})),
    );
  }
}
