import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {SearchResultsDto} from '../models/search-item.dto';

@Injectable({providedIn: 'root'})
export class SearchResultsService {
  constructor(private readonly http: HttpClient) {}

  query(phrase: string, page: number, types: string[]): Observable<SearchResultsDto | null> {
    let params = new HttpParams()
      .set('phrase', phrase)
      .set('page', String(page));
    if (types.length > 0) {
      params = params.set('types', types.join(','));
    }
    return this.http.get<SearchResultsDto>('/searchresults/', {params}).pipe(
      catchError(() => of(null)),
    );
  }
}
