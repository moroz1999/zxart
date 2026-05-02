import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdSummariesPayload, ProdSummaryDto} from '../models/prod-summary.dto';

@Injectable({providedIn: 'root'})
export class ProdCompilationItemsApiService {
  constructor(private readonly http: HttpClient) {}

  getProds(elementId: number): Observable<ProdSummaryDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdSummariesPayload>('/prod-compilation-items/', {params}).pipe(
      map(response => response.prods ?? []),
      catchError(() => of([])),
    );
  }
}
