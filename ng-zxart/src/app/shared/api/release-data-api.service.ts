import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EntityChangeResult} from '../models/entity-change-result';
import {CreatedIdsResult, ReleaseCreateFromFilesRequest, ReleaseCreateUploads, ReleaseSaveRequest, ReleaseUploads} from '../models/entity-save';
import {entitySaveBody} from './entity-save-body';

const ENDPOINT = '/release-data/';

/**
 * Changes to a release through its data endpoint. Errors arrive as HTTP
 * error statuses with an `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class ReleaseDataApiService {
  constructor(private readonly http: HttpClient) {}

  /** Saves the fields and lists the request carries; what it leaves out keeps its value. */
  update(id: number, request: ReleaseSaveRequest, uploads: ReleaseUploads = {}): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, entitySaveBody(request, {...uploads}), {params: {id, action: 'update'}});
  }

  /** Makes a release of each release file, or one release with none; answers the release ids. */
  createFromFiles(request: ReleaseCreateFromFilesRequest, uploads: ReleaseCreateUploads): Observable<CreatedIdsResult> {
    return this.http.post<CreatedIdsResult>(ENDPOINT, entitySaveBody(request, {...uploads}), {params: {action: 'createFromFiles'}});
  }

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
