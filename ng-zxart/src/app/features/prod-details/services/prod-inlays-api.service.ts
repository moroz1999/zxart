import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdReleaseInlayDto, ProdReleaseInlaysPayload} from '../models/prod-release-inlay.dto';

@Injectable({providedIn: 'root'})
export class ProdInlaysApiService {
  constructor(private readonly http: HttpClient) {}

  getInlays(elementId: number): Observable<ProdReleaseInlayDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdReleaseInlaysPayload>('/prod-inlays/', {params}).pipe(
      map(response => response.inlays ?? []),
      catchError(() => of([])),
    );
  }
}
