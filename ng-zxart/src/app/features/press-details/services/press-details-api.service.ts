import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, switchMap} from 'rxjs/operators';
import {PressDetailsDto} from '../models/press-details.dto';
import {LanguageService} from '../../settings/services/language.service';

@Injectable({providedIn: 'root'})
export class PressDetailsApiService {
  constructor(
    private readonly http: HttpClient,
    private readonly languageService: LanguageService,
  ) {}

  /**
   * Stays subscribed to the language: the article title, introduction and text
   * are multi-language fields, so a language switch refetches the article.
   * Consumers must therefore unsubscribe — the read view does so through the
   * `async` pipe.
   */
  getDetails(articleId: number): Observable<PressDetailsDto | null> {
    const params = new HttpParams().set('id', String(articleId));
    return this.languageService.languageCode$.pipe(
      switchMap(() => this.http.get<PressDetailsDto>('/press-details/', {params}).pipe(
        catchError(() => of(null)),
      )),
    );
  }
}
