import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {PictureSearchLocation} from '../models/picture-search-response';

interface AjaxSearchLocationItem {
  id: number;
  title: string;
}

interface AjaxSearchResponse {
  responseData: {
    country?: AjaxSearchLocationItem[];
    city?: AjaxSearchLocationItem[];
  };
}

/**
 * Country/city autocomplete backed by the legacy public /ajaxSearch/ endpoint
 * (same endpoint the legacy detailed search form used).
 */
@Injectable({
  providedIn: 'root',
})
export class LocationSearchService {
  constructor(private http: HttpClient) {}

  searchCountries(query: string): Observable<PictureSearchLocation[]> {
    return this.search('country', query);
  }

  searchCities(query: string): Observable<PictureSearchLocation[]> {
    return this.search('city', query);
  }

  private search(type: 'country' | 'city', query: string): Observable<PictureSearchLocation[]> {
    return this.http.get<AjaxSearchResponse>(`/ajaxSearch/mode:public/types:${type}/`, {
      params: {query},
    }).pipe(
      map(response => (response.responseData[type] ?? []).map(item => ({
        id: item.id,
        title: item.title,
      }))),
      catchError(() => of([])),
    );
  }
}
