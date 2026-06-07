import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';
import {ProdReleaseDto} from '../../prod-details/models/prod-release.dto';

export type GroupProdsScope = 'own' | 'published' | 'releases';

export interface GroupProdCategory {
  id: number;
  title: string;
}

export interface GroupProdEntry extends ZxProdDto {
  readonly type: 'prod';
}

export interface GroupReleaseEntry extends ProdReleaseDto {
  readonly type: 'release';
}

export type GroupProdItem = GroupProdEntry | GroupReleaseEntry;

export interface GroupProdsPage {
  items: GroupProdItem[];
  total: number;
  availableTypes: string[];
  availableCategories: GroupProdCategory[];
}

@Injectable({providedIn: 'root'})
export class GroupProdsApiService {
  constructor(private readonly http: HttpClient) {}

  getProds(
    elementId: number,
    scope: GroupProdsScope,
    start: number,
    limit: number,
    sort: string,
    sortDir: string,
    type: string,
    categoryId: number,
  ): Observable<GroupProdsPage> {
    let params = new HttpParams()
      .set('id', String(elementId))
      .set('scope', scope)
      .set('start', String(start))
      .set('limit', String(limit))
      .set('sort', sort)
      .set('sortDir', sortDir);
    if (type !== '') {
      params = params.set('type', type);
    }
    if (categoryId > 0) {
      params = params.set('category', String(categoryId));
    }
    return this.http.get<GroupProdsPage>('/group-prods/', {params}).pipe(
      catchError(() => of({items: [], total: 0, availableTypes: [], availableCategories: []})),
    );
  }
}
