import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {PictureDetailsDto, PictureRelatedRailKind} from '../models/picture-details.dto';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

@Injectable({providedIn: 'root'})
export class PictureDetailsApiService {
  constructor(private readonly http: HttpClient) {}

  getDetails(pictureId: number): Observable<PictureDetailsDto | null> {
    const params = new HttpParams().set('id', String(pictureId));
    return this.http.get<PictureDetailsDto>('/picture-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }

  /** Lazily fetches one related rail (author / tags / prod) as a separate request. */
  getRelated(pictureId: number, kind: PictureRelatedRailKind): Observable<ZxPictureDto[]> {
    const params = new HttpParams()
      .set('action', 'related')
      .set('kind', kind)
      .set('pictureId', String(pictureId));
    return this.http.get<{items: ZxPictureDto[]}>('/picturelist/', {params}).pipe(
      map(response => response?.items ?? []),
      catchError(() => of([])),
    );
  }

  logView(pictureId: number): Observable<void> {
    const params = new HttpParams().set('action', 'logView').set('id', String(pictureId));
    return this.http.post<void>('/pictures/', {}, {params}).pipe(
      catchError(() => of(undefined)),
    );
  }
}
