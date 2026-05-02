import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ProdReleaseDto, ProdReleasesPayload} from '../models/prod-release.dto';

@Injectable({providedIn: 'root'})
export class ProdReleasesApiService {
  constructor(private readonly http: HttpClient) {}

  getReleases(elementId: number): Observable<ProdReleaseDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdReleasesPayload>('/prod-releases/', {params}).pipe(
      map(response => response.releases ?? []),
      catchError(() => of([])),
    );
  }
}
