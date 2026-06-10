import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ZxProd} from '../../../shared/models/zx-prod';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';

@Injectable({providedIn: 'root'})
export class PartyProdsApiService {
  constructor(private readonly http: HttpClient) {}

  getProds(partyId: number, compoType: string): Observable<ZxProd[]> {
    const params = new HttpParams().set('id', String(partyId)).set('compoType', compoType);
    return this.http.get<ZxProdDto[]>('/party-prods/', {params}).pipe(
      map(dtos => dtos.map(dto => new ZxProd(dto))),
      catchError(() => of([])),
    );
  }
}
