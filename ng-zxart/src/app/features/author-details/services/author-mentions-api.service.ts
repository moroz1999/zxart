import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {PressArticlePreviewDto, PressArticlesPayload} from '../../prod-details/models/press-article.dto';

@Injectable({providedIn: 'root'})
export class AuthorMentionsApiService {
  constructor(private readonly http: HttpClient) {}

  getMentions(authorId: number): Observable<PressArticlePreviewDto[]> {
    const params = new HttpParams().set('id', String(authorId));
    return this.http.get<PressArticlesPayload>('/author-mentions/', {params}).pipe(
      map(response => response.articles ?? []),
      catchError(() => of([])),
    );
  }
}
