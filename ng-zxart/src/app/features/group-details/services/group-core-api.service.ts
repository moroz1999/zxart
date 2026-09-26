import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, switchMap} from 'rxjs/operators';
import {GroupCoreDto} from '../models/group-core.dto';
import {LanguageService} from '../../settings/services/language.service';

@Injectable({providedIn: 'root'})
export class GroupCoreApiService {
  constructor(
    private readonly http: HttpClient,
    private readonly languageService: LanguageService,
  ) {}

  getCore(elementId: number): Observable<GroupCoreDto | null> {
    const params = new HttpParams().set('id', String(elementId));
    return this.languageService.languageCode$.pipe(
      switchMap(() => this.http.get<GroupCoreDto>('/group-details/', {params}).pipe(
        catchError(() => of(null)),
      )),
    );
  }
}
