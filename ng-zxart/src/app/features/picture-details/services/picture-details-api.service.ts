import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {PictureDetailsDto} from '../models/picture-details.dto';

@Injectable({providedIn: 'root'})
export class PictureDetailsApiService {
  constructor(private readonly http: HttpClient) {}

  getDetails(pictureId: number): Observable<PictureDetailsDto | null> {
    const params = new HttpParams().set('id', String(pictureId));
    return this.http.get<PictureDetailsDto>('/picture-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }

  logView(pictureId: number): Observable<void> {
    const params = new HttpParams().set('action', 'logView').set('id', String(pictureId));
    return this.http.post<void>('/pictures/', {}, {params}).pipe(
      catchError(() => of(undefined)),
    );
  }
}
