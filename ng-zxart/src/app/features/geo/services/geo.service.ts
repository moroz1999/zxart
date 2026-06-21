import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {
  GeoAuthorItem,
  GeoEntityType,
  GeoGroupItem,
  GeoListResponse,
  GeoMapResponse,
  GeoPartyItem,
  GeoBounds,
} from '../models/geo.models';

type GeoListItem = GeoAuthorItem | GeoGroupItem | GeoPartyItem;

@Injectable({
  providedIn: 'root',
})
export class GeoService {
  readonly map$: Observable<GeoMapResponse> = this.http.get<GeoMapResponse>('/geo/', {params: {action: 'map'}}).pipe(
    catchError(() => of({countries: [], counters: {authors: 0, groups: 0, parties: 0}})),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(private readonly http: HttpClient) {}

  getList(
    type: GeoEntityType,
    start: number,
    limit: number,
    sorting: string,
    countryId: number | null,
    cityId: number | null,
    bounds: GeoBounds | null,
    search: string,
  ): Observable<GeoListResponse<GeoListItem>> {
    const params: Record<string, string> = {
      action: type,
      start: String(start),
      limit: String(limit),
      sorting,
    };
    if (countryId !== null) {
      params['countryId'] = String(countryId);
    }
    if (cityId !== null) {
      params['cityId'] = String(cityId);
    }
    if (bounds !== null) {
      params['north'] = String(bounds.north);
      params['south'] = String(bounds.south);
      params['east'] = String(bounds.east);
      params['west'] = String(bounds.west);
    }
    if (search !== '') {
      params['search'] = search;
    }

    return this.http.get<GeoListResponse<GeoListItem>>('/geo/', {params}).pipe(
      catchError(() => of({total: 0, items: []})),
    );
  }
}
