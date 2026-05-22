import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ReleaseDetailsDto} from '../models/release-details.dto';

@Injectable({providedIn: 'root'})
export class ReleaseDetailsApiService {
  constructor(private readonly http: HttpClient) {}

  getDetails(releaseId: number): Observable<ReleaseDetailsDto | null> {
    const params = new HttpParams().set('id', String(releaseId));
    return this.http.get<ReleaseDetailsDto>('/release-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }
}
