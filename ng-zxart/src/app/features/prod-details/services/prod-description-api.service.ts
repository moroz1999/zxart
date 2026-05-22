import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {ProdDescriptionDto} from '../models/prod-description.dto';

@Injectable({providedIn: 'root'})
export class ProdDescriptionApiService {
  private readonly cache = new Map<number, Observable<ProdDescriptionDto | null>>();

  constructor(private readonly http: HttpClient) {}

  getDescription(elementId: number): Observable<ProdDescriptionDto | null> {
    const cached = this.cache.get(elementId);
    if (cached) {
      return cached;
    }
    const params = new HttpParams().set('id', String(elementId));
    const request$ = this.http.get<ProdDescriptionDto>('/prod-description/', {params}).pipe(
      catchError(() => of(null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.cache.set(elementId, request$);
    return request$;
  }
}
