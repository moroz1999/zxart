import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdSeriesEntryDto, ProdSeriesPayload} from '../models/prod-summary.dto';

@Injectable({providedIn: 'root'})
export class ProdSeriesApiService {
  constructor(private readonly http: HttpClient) {}

  getSeries(elementId: number): Observable<ProdSeriesEntryDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdSeriesPayload>('/prod-series/', {params}).pipe(
      map(response => response.series ?? []),
      catchError(() => of([])),
    );
  }
}
