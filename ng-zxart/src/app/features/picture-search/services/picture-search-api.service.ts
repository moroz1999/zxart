import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {PictureSearchFilters} from '../models/picture-search-filters';
import {
  PictureSearchLocation,
  PictureSearchLocationsResponse,
  PictureSearchResponse,
} from '../models/picture-search-response';

@Injectable({
  providedIn: 'root',
})
export class PictureSearchApiService {
  constructor(private http: HttpClient) {}

  /** Returns null on error so the component can show the error state. */
  search(filters: PictureSearchFilters, start: number, limit: number): Observable<PictureSearchResponse | null> {
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
    this.setIfPresent(params, 'pictureType', filters.pictureType);
    if (filters.realtime) {
      params['realtime'] = '1';
    }
    if (filters.inspiration) {
      params['inspiration'] = '1';
    }
    if (filters.stages) {
      params['stages'] = '1';
    }
    this.setIfPresent(params, 'tagsInclude', filters.tagsInclude.join(','));
    this.setIfPresent(params, 'tagsExclude', filters.tagsExclude.join(','));
    this.setIfPresent(params, 'authorCountry', filters.authorCountryIds.join(','));
    this.setIfPresent(params, 'authorCity', filters.authorCityIds.join(','));

    return this.http.get<PictureSearchResponse>('/picture-search/', {params}).pipe(
      catchError(() => of(null)),
    );
  }

  resolveLocations(ids: number[]): Observable<PictureSearchLocation[]> {
    if (ids.length === 0) {
      return of([]);
    }
    return this.http.get<PictureSearchLocationsResponse>('/picture-search/', {
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
