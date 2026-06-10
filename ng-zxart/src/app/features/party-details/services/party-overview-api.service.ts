import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ZxProd} from '../../../shared/models/zx-prod';
import {PartyOverview, PartyOverviewResponse} from '../models/party-overview.dto';

@Injectable({providedIn: 'root'})
export class PartyOverviewApiService {
  constructor(private readonly http: HttpClient) {}

  getOverview(partyId: number): Observable<PartyOverview> {
    const params = new HttpParams().set('id', String(partyId));
    return this.http.get<PartyOverviewResponse>('/party-overview/', {params}).pipe(
      map(response => ({
        prods: (response.prods ?? []).map(dto => new ZxProd(dto)),
        pictures: response.pictures ?? [],
        tunes: response.tunes ?? [],
      })),
      catchError(() => of({prods: [], pictures: [], tunes: []})),
    );
  }
}
