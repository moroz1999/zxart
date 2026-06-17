import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {FeedbackRequest, FeedbackResponse} from '../models/feedback-request';

@Injectable({
  providedIn: 'root',
})
export class FeedbackApiService {
  private readonly apiUrl = '/feedback/';

  constructor(private readonly http: HttpClient) {}

  submit(elementId: number, request: FeedbackRequest): Observable<FeedbackResponse> {
    return this.http.post<FeedbackResponse>(this.apiUrl, request, {
      params: {id: String(elementId)},
    });
  }
}
