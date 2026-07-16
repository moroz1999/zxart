import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {FeedbackRequest, FeedbackResponse} from '../models/feedback-request';

@Injectable({
  providedIn: 'root',
})
export class FeedbackApiService {
  private readonly apiUrl = '/feedback-data/';

  constructor(private readonly http: HttpClient) {}

  submit(elementId: number, request: FeedbackRequest): Observable<FeedbackResponse> {
    // elementId 0 = SPA mount: the backend resolves the feedback form by type.
    const params: Record<string, string> = elementId > 0 ? {id: String(elementId)} : {};
    return this.http.post<FeedbackResponse>(this.apiUrl, request, {params});
  }
}
