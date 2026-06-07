import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {GroupCoreDto} from '../models/group-core.dto';

@Injectable({providedIn: 'root'})
export class GroupCoreApiService {
  constructor(private readonly http: HttpClient) {}

  getCore(elementId: number): Observable<GroupCoreDto | null> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<GroupCoreDto>('/group-details/', {params}).pipe(
      catchError(() => of(null)),
    );
  }
}
