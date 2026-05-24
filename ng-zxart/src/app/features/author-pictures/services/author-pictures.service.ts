import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

export interface AuthorPicturesPage {
  items: ZxPictureDto[];
  total: number;
  availableFormats: string[];
}

@Injectable({
  providedIn: 'root'
})
export class AuthorPicturesService {
  constructor(private http: HttpClient) {}

  getPictures(elementId: number): Observable<ZxPictureDto[]> {
    return this.http.get<ZxPictureDto[]>('/pictures/', {
      params: {action: 'picturesByElement', elementId: String(elementId)},
    }).pipe(catchError(() => of([])));
  }

  getPicturesPaged(
    elementId: number,
    start: number,
    limit: number,
    sortColumn: string,
    sortDir: string,
    format = '',
  ): Observable<AuthorPicturesPage> {
    let params = new HttpParams()
      .set('action', 'picturesByElement')
      .set('elementId', String(elementId))
      .set('start', String(start))
      .set('limit', String(limit))
      .set('sortColumn', sortColumn)
      .set('sortDir', sortDir);
    if (format) {
      params = params.set('format', format);
    }
    return this.http.get<AuthorPicturesPage>('/pictures/', {params}).pipe(
      catchError(() => of({items: [], total: 0, availableFormats: []})),
    );
  }
}
