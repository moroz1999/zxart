import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map, switchMap} from 'rxjs/operators';
import {LanguageService} from '../../settings/services/language.service';

/** Fetches static, non-editable content pages (About, FAQ, support, API). */
@Injectable({
  providedIn: 'root',
})
export class ContentService {
  constructor(
    private readonly http: HttpClient,
    private readonly languageService: LanguageService,
  ) {}

  /** Returns the page HTML in the current interface language, or null on error. Re-emits when the language changes. */
  getContent(page: string): Observable<string | null> {
    return this.languageService.current$.pipe(
      switchMap(lang => this.http.get<{html: string}>('/content-data/', {params: {page, lang}}).pipe(
        map(response => response?.html ?? ''),
        catchError(() => of(null)),
      )),
    );
  }
}
