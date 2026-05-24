import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

export interface AuthorTunesPage {
  items: ZxTuneDto[];
  total: number;
  availableFormats: string[];
}

@Injectable({
  providedIn: 'root'
})
export class AuthorTunesService {
  constructor(private http: HttpClient) {}

  getTunes(elementId: number): Observable<ZxTuneDto[]> {
    return this.http.get<ZxTuneDto[]>('/tunes/', {
      params: {action: 'tunesByElement', elementId: String(elementId)},
    }).pipe(catchError(() => of([])));
  }

  getTunesPaged(
    elementId: number,
    start: number,
    limit: number,
    sortColumn: string,
    sortDir: string,
    format = '',
  ): Observable<AuthorTunesPage> {
    let params = new HttpParams()
      .set('action', 'tunesByElement')
      .set('elementId', String(elementId))
      .set('start', String(start))
      .set('limit', String(limit))
      .set('sortColumn', sortColumn)
      .set('sortDir', sortDir);
    if (format) {
      params = params.set('format', format);
    }
    return this.http.get<AuthorTunesPage>('/tunes/', {params}).pipe(
      catchError(() => of({items: [], total: 0, availableFormats: []})),
    );
  }
}
