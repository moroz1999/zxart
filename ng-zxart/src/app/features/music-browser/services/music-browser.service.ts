import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

export interface PaginatedTunesResponse {
  total: number;
  items: ZxTuneDto[];
}

@Injectable({
  providedIn: 'root',
})
export class MusicBrowserService {
  constructor(private http: HttpClient) {}

  /**
   * `filter` names one of the backend's collection filters, so the top-music
   * subsets need no element id in their URL; it replaces the element browsing.
   */
  getPaged(
    elementId: number,
    filter: string | null,
    start: number,
    limit: number,
    sorting: string,
    linkType = 'structure',
  ): Observable<PaginatedTunesResponse> {
    const params: Record<string, string> = {
      elementId: String(elementId),
      start: String(start),
      limit: String(limit),
      sorting,
      linkType,
    };
    if (filter) {
      params['filter'] = filter;
    }
    return this.http.get<PaginatedTunesResponse>('/musiclist/', {params}).pipe(
      catchError(() => of({total: 0, items: []}))
    );
  }
}
