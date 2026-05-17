import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdFileDto, ProdFilesPayload} from '../models/prod-file.dto';

@Injectable({providedIn: 'root'})
export class ProdScreenshotMoveApiService {
  constructor(private readonly http: HttpClient) {}

  move(elementId: number, fileId: number, direction: 'left' | 'right'): Observable<ProdFileDto[] | null> {
    const body = new HttpParams()
      .set('id', String(elementId))
      .set('fileId', String(fileId))
      .set('direction', direction);
    return this.http.post<ProdFilesPayload>('/prod-screenshot-move/', body.toString(), {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    }).pipe(
      map(response => response.files ?? []),
      catchError(() => of(null)),
    );
  }
}
