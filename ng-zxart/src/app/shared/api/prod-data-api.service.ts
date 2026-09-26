import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {EntityChangeResult} from '../models/entity-change-result';

const ENDPOINT = '/prod-data/';

/**
 * Changes to a production through its data endpoint. Errors arrive as HTTP
 * error statuses with an `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class ProdDataApiService {
  constructor(private readonly http: HttpClient) {}

  delete(id: number): Observable<EntityChangeResult> {
    return this.run(id, 'delete');
  }

  private run(id: number, action: string): Observable<EntityChangeResult> {
    return this.http.post<EntityChangeResult>(ENDPOINT, null, {params: {id, action}});
  }
}
