import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {TranslateService} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';

/** Fetches static, non-editable content pages (About, FAQ, support, API). */
@Injectable({
  providedIn: 'root',
})
export class ContentService {
  constructor(
    private readonly http: HttpClient,
    private readonly translate: TranslateService,
  ) {}

  /** Returns the page HTML, or null on error. */
  getContent(page: string): Observable<string | null> {
    const lang = this.translate.currentLang || this.translate.defaultLang || 'en';
    return this.http.get<{html: string}>('/content-data/', {params: {page, lang}}).pipe(
      map(response => response?.html ?? ''),
      catchError(() => of(null)),
    );
  }
}
