import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdFileDto, ProdFilesPayload} from '../models/prod-file.dto';

@Injectable({providedIn: 'root'})
export class ProdScreenshotsApiService {
  constructor(private readonly http: HttpClient) {}

  getScreenshots(elementId: number): Observable<ProdFileDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdFilesPayload>('/prod-screenshots/', {params}).pipe(
      map(response => response.files ?? []),
      catchError(() => of([])),
    );
  }
}
