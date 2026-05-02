import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ProdMapsPayload} from '../models/prod-file.dto';

const EMPTY_MAPS: ProdMapsPayload = {files: [], mapsUrl: undefined};

@Injectable({providedIn: 'root'})
export class ProdMapsApiService {
  constructor(private readonly http: HttpClient) {}

  getMaps(elementId: number): Observable<ProdMapsPayload> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdMapsPayload>('/prod-maps/', {params}).pipe(
      catchError(() => of(EMPTY_MAPS)),
    );
  }
}
