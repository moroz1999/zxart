import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdFileDto, ProdFilesPayload} from '../models/prod-file.dto';

@Injectable({providedIn: 'root'})
export class ReleaseScreenshotsApiService {
  constructor(private readonly http: HttpClient) {}

  getScreenshots(releaseId: number): Observable<ProdFileDto[]> {
    const params = new HttpParams().set('id', String(releaseId));
    return this.http.get<ProdFilesPayload>('/release-screenshots/', {params}).pipe(
      map(response => response.files ?? []),
      catchError(() => of([])),
    );
  }
}
