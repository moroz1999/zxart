import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdCoverGroupDto, ProdCoversPayload} from '../models/prod-release-inlay.dto';

@Injectable({providedIn: 'root'})
export class ProdInlaysApiService {
  constructor(private readonly http: HttpClient) {}

  getCoverGroups(elementId: number): Observable<ProdCoverGroupDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdCoversPayload>('/prod-inlays/', {params}).pipe(
      map(response => response.groups ?? []),
      catchError(() => of([])),
    );
  }
}
