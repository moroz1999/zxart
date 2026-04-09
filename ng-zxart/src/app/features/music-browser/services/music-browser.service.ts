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

  getPaged(elementId: number, start: number, limit: number, sorting: string): Observable<PaginatedTunesResponse> {
    return this.http.get<PaginatedTunesResponse>('/musiclist/', {
      params: {elementId: String(elementId), start: String(start), limit: String(limit), sorting},
    }).pipe(
      catchError(() => of({total: 0, items: []}))
    );
  }
}
