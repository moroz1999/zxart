import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EntityChangeResult} from '../models/entity-change-result';
import {AuthorAliasSaveRequest} from '../models/entity-save';
import {entitySaveBody} from './entity-save-body';

const ENDPOINT = '/author-alias-data/';

/**
 * Changes to an author alias through its data endpoint. Errors arrive as HTTP
 * error statuses with an `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class AuthorAliasDataApiService {
  constructor(private readonly http: HttpClient) {}

  /** Creates the entity; answers its id. */
  create(request: AuthorAliasSaveRequest): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, entitySaveBody(request, {}), {params: {action: 'create'}});
  }

  /** Saves the fields and lists the request carries; what it leaves out keeps its value. */
  update(id: number, request: AuthorAliasSaveRequest): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, entitySaveBody(request, {}), {params: {id, action: 'update'}});
  }

  delete(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'delete');
  }

  /** Turns the alias into a standalone author; answers the author id. */
  convertToAuthor(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'convertToAuthor');
  }

  private run(id: number, action: string): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, null, {params: {id, action}});
  }
}
