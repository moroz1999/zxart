import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';
import {ProdReleaseDto} from '../../prod-details/models/prod-release.dto';

export interface AuthorProdEntry extends ZxProdDto {
  readonly type: 'prod';
  readonly rolesInProd: string[];
}

export interface AuthorReleaseEntry extends ProdReleaseDto {
  readonly type: 'release';
  readonly rolesInProd: string[];
}

export type AuthorProdItem = AuthorProdEntry | AuthorReleaseEntry;

export interface AuthorProdsPage {
  items: AuthorProdItem[];
  total: number;
  availableRoles: string[];
}

@Injectable({providedIn: 'root'})
export class AuthorProdsApiService {
  constructor(private readonly http: HttpClient) {}

  getProds(
    elementId: number,
    start: number,
    limit: number,
    sort: string,
    sortDir: string,
    role: string,
  ): Observable<AuthorProdsPage> {
    let params = new HttpParams()
      .set('id', String(elementId))
      .set('start', String(start))
      .set('limit', String(limit))
      .set('sort', sort)
      .set('sortDir', sortDir);
    if (role !== '') {
      params = params.set('role', role);
    }
    return this.http.get<AuthorProdsPage>('/author-prods/', {params}).pipe(
      catchError(() => of({items: [], total: 0, availableRoles: []})),
    );
  }
}
