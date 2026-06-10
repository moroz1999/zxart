import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

@Injectable({providedIn: 'root'})
export class PartyMusicApiService {
  constructor(private readonly http: HttpClient) {}

  getTunes(partyId: number, compoType: string): Observable<ZxTuneDto[]> {
    const params = new HttpParams().set('id', String(partyId)).set('compoType', compoType);
    return this.http.get<ZxTuneDto[]>('/party-music/', {params}).pipe(
      catchError(() => of([])),
    );
  }
}
