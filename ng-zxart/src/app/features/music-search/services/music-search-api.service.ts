import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {MusicSearchFilters} from '../models/music-search-filters';
import {
  MusicSearchLocation,
  MusicSearchLocationsResponse,
  MusicSearchResponse,
} from '../models/music-search-response';

@Injectable({
  providedIn: 'root',
})
export class MusicSearchApiService {
  constructor(private http: HttpClient) {}

  search(filters: MusicSearchFilters, start: number, limit: number): Observable<MusicSearchResponse | null> {
    const params: Record<string, string> = {
      start: String(start),
      limit: String(limit),
      resultsType: filters.resultsType,
      sortParameter: filters.sortParameter,
      sortOrder: filters.sortOrder,
    };
    this.setIfPresent(params, 'titleWord', filters.titleWord);
    this.setIfPresent(params, 'startYear', filters.startYear);
    this.setIfPresent(params, 'endYear', filters.endYear);
    this.setIfPresent(params, 'rating', filters.rating);
    this.setIfPresent(params, 'partyPlace', filters.partyPlace);
    this.setIfPresent(params, 'formatGroup', filters.formatGroup);
    this.setIfPresent(params, 'format', filters.format);
    if (filters.realtime) {
      params['realtime'] = '1';
    }
    this.setIfPresent(params, 'tagsInclude', filters.tagsInclude.join(','));
    this.setIfPresent(params, 'tagsExclude', filters.tagsExclude.join(','));
    this.setIfPresent(params, 'authorCountry', filters.authorCountryIds.join(','));
    this.setIfPresent(params, 'authorCity', filters.authorCityIds.join(','));

    return this.http.get<MusicSearchResponse>('/music-search/', {params}).pipe(
      catchError(() => of(null)),
    );
  }

  resolveLocations(ids: number[]): Observable<MusicSearchLocation[]> {
    if (ids.length === 0) {
      return of([]);
    }
    return this.http.get<MusicSearchLocationsResponse>('/music-search/', {
      params: {action: 'locations', ids: ids.join(',')},
    }).pipe(
      map(response => response.items),
      catchError(() => of([])),
    );
  }

  private setIfPresent(params: Record<string, string>, name: string, value: string): void {
    const trimmed = value.trim();
    if (trimmed !== '') {
      params[name] = trimmed;
    }
  }
}
