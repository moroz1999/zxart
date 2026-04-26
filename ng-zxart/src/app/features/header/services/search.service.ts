import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {SearchResultDto, SearchResultGroup} from '../models/search-result.dto';

const SEARCH_TYPES = 'author,authorAlias,party,group,groupAlias,zxProd,zxPicture,zxMusic,pressArticle';

const ALIAS_TO_PUBLIC_TYPE: Record<string, string> = {
  authorAlias: 'author',
  groupAlias: 'group',
};

const PUBLIC_TYPE_ORDER = ['author', 'party', 'group', 'zxProd', 'zxPicture', 'zxMusic', 'pressArticle'];

const TYPE_ICONS: Record<string, string> = {
  author: 'person',
  party: 'list',
  group: 'person',
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
    const buckets = new Map<string, SearchResultDto[]>();
    for (const rawType of SEARCH_TYPES.split(',')) {
      const items = data[rawType];
      if (!Array.isArray(items) || items.length === 0) {
        continue;
      }
      const publicType = ALIAS_TO_PUBLIC_TYPE[rawType] ?? rawType;
      const normalized = items.map(item => ({
        ...item,
        title: (item as unknown as {searchTitle?: string}).searchTitle ?? item.title,
      }));
      const existing = buckets.get(publicType);
      if (existing) {
        existing.push(...normalized);
      } else {
        buckets.set(publicType, normalized);
      }
    }
    return PUBLIC_TYPE_ORDER
      .filter(type => buckets.has(type))
      .map(type => ({
        type,
        icon: TYPE_ICONS[type] ?? 'list',
        items: buckets.get(type)!,
      }));
  }
}
