import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {TuneDetailsDto, TuneRelatedRailKind} from '../models/tune-details.dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

@Injectable({providedIn: 'root'})
export class TuneDetailsApiService {
  constructor(private readonly http: HttpClient) {}

  getDetails(tuneId: number): Observable<TuneDetailsDto | null> {
    const params = new HttpParams().set('id', String(tuneId));
    return this.http.get<TuneDetailsDto>('/tune-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }

  /** Lazily fetches one related rail (author / tags / tracker) as a separate request. */
  getRelated(tuneId: number, kind: TuneRelatedRailKind): Observable<ZxTuneDto[]> {
    const params = new HttpParams()
      .set('action', 'related')
      .set('kind', kind)
      .set('tuneId', String(tuneId));
    return this.http.get<{items: ZxTuneDto[]}>('/musiclist/', {params}).pipe(
      map(response => response?.items ?? []),
      catchError(() => of([])),
    );
  }
}
