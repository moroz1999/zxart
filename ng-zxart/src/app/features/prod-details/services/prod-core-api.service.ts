import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ProdCoreDto} from '../models/prod-core.dto';

@Injectable({providedIn: 'root'})
export class ProdCoreApiService {
  constructor(private readonly http: HttpClient) {}

  getCore(elementId: number): Observable<ProdCoreDto | null> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdCoreDto>('/prod-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }
}
