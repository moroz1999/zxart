import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay, switchMap} from 'rxjs/operators';
import {ProdDescriptionDto} from '../models/prod-description.dto';
import {LanguageService} from '../../settings/services/language.service';

@Injectable({providedIn: 'root'})
export class ProdDescriptionApiService {
  private readonly cache = new Map<string, Observable<ProdDescriptionDto | null>>();

  constructor(
    private readonly http: HttpClient,
    private readonly languageService: LanguageService,
  ) {}

  /**
   * Stays subscribed to the language so a language switch refetches the
   * description in the newly selected language. Consumers must therefore
   * unsubscribe — the description and instructions blocks do so through the `async` pipe.
   */
  getDescription(elementId: number): Observable<ProdDescriptionDto | null> {
    return this.languageService.languageCode$.pipe(
      switchMap(languageCode => this.fetch(elementId, languageCode)),
    );
  }

  private fetch(elementId: number, languageCode: string): Observable<ProdDescriptionDto | null> {
    const key = `${elementId}:${languageCode}`;
    const cached = this.cache.get(key);
    if (cached) {
      return cached;
    }
    const params = new HttpParams().set('id', String(elementId)).set('lang', languageCode);
    const request$ = this.http.get<ProdDescriptionDto>('/prod-description/', {params}).pipe(
      catchError(() => of(null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.cache.set(key, request$);
    return request$;
  }
}
