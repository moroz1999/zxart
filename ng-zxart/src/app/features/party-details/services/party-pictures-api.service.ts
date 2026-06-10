import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

@Injectable({providedIn: 'root'})
export class PartyPicturesApiService {
  constructor(private readonly http: HttpClient) {}

  getPictures(partyId: number, compoType: string): Observable<ZxPictureDto[]> {
    const params = new HttpParams().set('id', String(partyId)).set('compoType', compoType);
    return this.http.get<ZxPictureDto[]>('/party-pictures/', {params}).pipe(
      catchError(() => of([])),
    );
  }
}
