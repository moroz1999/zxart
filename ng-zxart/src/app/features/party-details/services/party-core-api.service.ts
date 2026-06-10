import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PartyCoreDto} from '../models/party-core.dto';

@Injectable({providedIn: 'root'})
export class PartyCoreApiService {
  constructor(private readonly http: HttpClient) {}

  getCore(elementId: number): Observable<PartyCoreDto | null> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<PartyCoreDto>('/party-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }
}
