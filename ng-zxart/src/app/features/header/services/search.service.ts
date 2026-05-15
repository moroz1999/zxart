import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {SearchResultDto, SearchResultGroup} from '../models/search-result.dto';
import {SearchItemDto, SearchResultsDto} from '../../search-results/models/search-item.dto';

const SEARCH_TYPES = ['author', 'party', 'group', 'zxProd', 'zxPicture', 'zxMusic', 'pressArticle'];
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
    const params = new HttpParams()
      .set('phrase', query.trim())
      .set('types', SEARCH_TYPES.join(','))
      .set('mode', 'quick');
    return this.http.get<SearchResultsDto>('/searchresults/', {params}).pipe(
      map(response => this.group(response)),
      catchError(() => of([])),
    );
  }

  private group(response: SearchResultsDto): SearchResultGroup[] {
    const buckets = new Map<string, SearchResultDto[]>();
    for (const set of response.sets) {
      if (!Array.isArray(set.items) || set.items.length === 0) {
        continue;
      }
      const normalized = set.items.map(item => this.toHeaderResult(item));
      const existing = buckets.get(set.type);
      if (existing) {
        existing.push(...normalized);
      } else {
        buckets.set(set.type, normalized);
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

  private toHeaderResult(item: SearchItemDto): SearchResultDto {
    return {
      title: item.title,
      url: 'url' in item ? item.url : undefined,
    };
  }
}
