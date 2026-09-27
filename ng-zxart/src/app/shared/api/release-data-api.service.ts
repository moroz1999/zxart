import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EntityChangeResult} from '../models/entity-change-result';

const ENDPOINT = '/release-data/';

/**
 * Changes to a release through its data endpoint. Errors arrive as HTTP
 * error statuses with an `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class ReleaseDataApiService {
  constructor(private readonly http: HttpClient) {}

  delete(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'delete');
  }

  /** Removes the author from the members. */
  deleteMember(id: number, authorId: number): Observable<EntityChangeResult> {
    return this.run(id, 'deleteMember', {authorId});
  }

  /** Deletes one file of a multi-file selector. */
  deleteFile(id: number, fileId: number): Observable<EntityChangeResult> {
    return this.run(id, 'deleteFile', {fileId});
  }

  private run(
    id: number,
    action: string,
    params: Readonly<Record<string, number>> = {},
  ): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, null, {params: {id, action, ...params}});
  }
}
