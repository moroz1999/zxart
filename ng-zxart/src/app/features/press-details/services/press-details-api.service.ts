import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PressDetailsDto} from '../models/press-details.dto';

@Injectable({providedIn: 'root'})
export class PressDetailsApiService {
  constructor(private readonly http: HttpClient) {}

  getDetails(articleId: number): Observable<PressDetailsDto | null> {
    const params = new HttpParams().set('id', String(articleId));
    return this.http.get<PressDetailsDto>('/press-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }
}
