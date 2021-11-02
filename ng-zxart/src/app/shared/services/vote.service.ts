import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {JsonResponse} from '../models/json-response';
import {VoteElements} from '../models/vote-elements-response-data';
import {map} from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})
export class VoteService {
  private apiUrl: string = `//${location.hostname}/ajax/`;

  constructor(private http: HttpClient) {
  }

  public send<T extends string>(elementId: number, vote: number, type: T) {
    const parameters = {
      id: elementId,
      action: 'vote',
      value: vote,
    };
    return this.http
      .get<JsonResponse<VoteElements<T>>>(this.apiUrl, {'params': parameters} as Object)
      .pipe(
        map(response => {
          return response.responseData[type] ? response.responseData[type][0].votes : 0;
        }),
      );
  }
}
