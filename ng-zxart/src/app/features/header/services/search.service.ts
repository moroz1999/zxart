import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {SearchResultDto, SearchResultGroup} from '../models/search-result.dto';

const SEARCH_TYPES = 'author,authorAlias,party,group,groupAlias,zxProd,zxPicture,zxMusic,pressArticle';

const TYPE_ICONS: Record<string, string> = {
  author: 'person',
  authorAlias: 'person',
  party: 'list',
  group: 'person',
  groupAlias: 'person',
  zxProd: 'videogame-asset',
  zxPicture: 'image',
  zxMusic: 'music-note',
  pressArticle: 'list',
};

@Injectable({
  providedIn: 'root',
})
export class SearchService {
  constructor(private http: HttpClient) {}

  search(query: string): Observable<SearchResultGroup[]> {
    const encoded = encodeURIComponent(query.trim());
    const url = `/ajaxSearch/mode:public/types:${SEARCH_TYPES}/totals:1/query:${encoded}/`;
    return this.http.get<{responseData: Record<string, SearchResultDto[]>}>(url).pipe(
      map(response => this.group(response.responseData)),
      catchError(() => of([])),
    );
  }

  private group(data: Record<string, SearchResultDto[]>): SearchResultGroup[] {
    return SEARCH_TYPES.split(',')
      .filter(t => Array.isArray(data[t]) && data[t].length > 0)
      .map(t => ({
        type: t,
        icon: TYPE_ICONS[t] ?? 'list',
        items: data[t].map(item => ({
          ...item,
          title: (item as any)['searchTitle'] ?? item.title,
        })),
      }));
  }
}
