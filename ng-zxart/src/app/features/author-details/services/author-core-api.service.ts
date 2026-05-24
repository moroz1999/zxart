import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {AuthorCoreDto} from '../models/author-core.dto';

@Injectable({providedIn: 'root'})
export class AuthorCoreApiService {
  constructor(private readonly http: HttpClient) {}

  getCore(elementId: number): Observable<AuthorCoreDto | null> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<AuthorCoreDto>('/author-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }
}
