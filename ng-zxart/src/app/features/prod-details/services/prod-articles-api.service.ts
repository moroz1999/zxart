import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {PressArticlePreviewDto, PressArticlesPayload} from '../models/press-article.dto';

@Injectable({providedIn: 'root'})
export class ProdArticlesApiService {
  constructor(private readonly http: HttpClient) {}

  getArticles(elementId: number): Observable<PressArticlePreviewDto[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<PressArticlesPayload>('/prod-articles/', {params}).pipe(
      map(response => response.articles ?? []),
      catchError(() => of([])),
    );
  }
}
