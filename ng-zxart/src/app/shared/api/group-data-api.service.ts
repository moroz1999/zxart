import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EntityChangeResult} from '../models/entity-change-result';
import {GroupSaveRequest, ImageUpload} from '../models/entity-save';
import {entitySaveBody} from './entity-save-body';

const ENDPOINT = '/group-data/';

/**
 * Changes to a group through its data endpoint. Errors arrive as HTTP
 * error statuses with an `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class GroupDataApiService {
  constructor(private readonly http: HttpClient) {}

  /** Creates the entity; answers its id. */
  create(request: GroupSaveRequest, uploads: ImageUpload = {}): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, entitySaveBody(request, {...uploads}), {params: {action: 'create'}});
  }

  /** Saves the fields and lists the request carries; what it leaves out keeps its value. */
  update(id: number, request: GroupSaveRequest, uploads: ImageUpload = {}): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, entitySaveBody(request, {...uploads}), {params: {id, action: 'update'}});
  }

  delete(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'delete');
  }

  /** Turns the group into a new author; answers the author id. */
  convertToAuthor(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'convertToAuthor');
  }

  /** Removes the author from the members. */
  deleteMember(id: number, authorId: number): Observable<EntityChangeResult> {
    return this.run(id, 'deleteMember', {authorId});
  }

  private run(
    id: number,
    action: string,
    params: Readonly<Record<string, number>> = {},
  ): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, null, {params: {id, action, ...params}});
  }
}
