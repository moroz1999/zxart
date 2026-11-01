import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {AssistantConversation, AssistantPage, AssistantRunStarted, AssistantState} from '../models/assistant-data';

const ENDPOINT = '/assistant-data/';

/**
 * The editors' AI assistant. Errors arrive as HTTP error statuses with an
 * `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class AssistantDataApiService {
  constructor(private readonly http: HttpClient) {}

  create(): Observable<AssistantConversation> {
    return this.http.post<AssistantConversation>(ENDPOINT, null, {params: {action: 'create'}});
  }

  /** The messages after `afterSeq`, every plan, the active run and the entities they mention. */
  get(conversationId: number, afterSeq: number): Observable<AssistantState> {
    return this.http.get<AssistantState>(ENDPOINT, {params: {action: 'get', id: conversationId, afterSeq}});
  }

  /** Stores the message and starts the run that answers it. */
  send(conversationId: number, text: string, page: AssistantPage): Observable<AssistantRunStarted> {
    return this.http.post<AssistantRunStarted>(ENDPOINT, {conversationId, text, page}, {params: {action: 'send'}});
  }

  /** Starts running a proposed plan. */
  approvePlan(planId: number): Observable<AssistantRunStarted> {
    return this.http.post<AssistantRunStarted>(ENDPOINT, {planId}, {params: {action: 'approvePlan'}});
  }
}
