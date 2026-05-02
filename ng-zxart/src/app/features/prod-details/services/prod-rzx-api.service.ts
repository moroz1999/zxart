import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdFileDto, ProdFilesPayload} from '../models/prod-file.dto';

@Injectable({providedIn: 'root'})
export class ProdRzxApiService {
  constructor(private readonly http: HttpClient) {}

  getRzx(elementId: number): Observable<ProdFileDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdFilesPayload>('/prod-rzx/', {params}).pipe(
      map(response => response.files ?? []),
      catchError(() => of([])),
    );
  }
}
