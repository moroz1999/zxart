import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EntityChangeResult} from '../models/entity-change-result';

const ENDPOINT = '/author-data/';

/**
 * Changes to an author through its data endpoint. Errors arrive as HTTP
 * error statuses with an `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class AuthorDataApiService {
  constructor(private readonly http: HttpClient) {}

  delete(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'delete');
  }

  /** Turns the author into a new group; answers the group id. */
  convertToGroup(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'convertToGroup');
  }

  private run(id: number, action: string): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, null, {params: {id, action}});
  }
}
