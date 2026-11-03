import {HttpErrorResponse} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {BehaviorSubject, combineLatest, concat, EMPTY, from, Observable, of, timer} from 'rxjs';
import {catchError, distinctUntilChanged, exhaustMap, map, shareReplay, startWith, switchMap, tap} from 'rxjs/operators';
import {AssistantDataApiService} from '../../../shared/api/assistant-data-api.service';
import {AssistantConversationSummary, AssistantPlan, AssistantRun, AssistantState} from '../../../shared/models/assistant-data';
import {AssistantView} from '../models/assistant-view';
import {AssistantPageContextService} from './assistant-page-context.service';
import {AssistantViewInput, buildAssistantView} from './assistant-view';
import {AssistantMarkdownRenderer, createAssistantMarkdown} from './assistant-markdown';

const FAST_POLL_MS = 2000;

interface StoreState extends AssistantViewInput {
  lastSeq: number;
}

/**
 * The open conversation: its messages merged by `seq`, its plans and run as the
 * last poll reported them. It starts on a new conversation, which is created
 * on the first message.
 */
@Injectable({providedIn: 'root'})
export class AssistantStore {
  private readonly state: BehaviorSubject<StoreState>;
  private readonly conversations = new BehaviorSubject<AssistantConversationSummary[]>([]);

  /** The user's conversations as last loaded by {@see loadConversations}. */
  readonly conversations$ = this.conversations.asObservable();

  readonly view$: Observable<AssistantView>;

  /**
   * While subscribed: fetches a conversation once when it is opened, then
   * every 2 s while a run is active. Nothing changes an idle conversation but
   * the user's own send, approve or stop, and each of those marks a run.
   */
  readonly polling$: Observable<void>;

  constructor(
    private readonly api: AssistantDataApiService,
    private readonly pageContext: AssistantPageContextService,
  ) {
    this.state = new BehaviorSubject<StoreState>(this.emptyState(null));
    // Markdown arrives in a chunk of its own; until then prose is plain text
    const markdown$: Observable<AssistantMarkdownRenderer | null> = from(import('marked')).pipe(
      map(markedModule => createAssistantMarkdown(markedModule)),
      catchError(() => of(null)),
      startWith(null),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.view$ = combineLatest([this.state, markdown$]).pipe(map(([state, markdown]) => buildAssistantView(state, markdown)));
    this.polling$ = this.state.pipe(
      map(state => state.conversationId),
      distinctUntilChanged(),
      switchMap(conversationId => conversationId === null ? EMPTY : concat(
        this.refresh(),
        this.state.pipe(
          map(state => state.activeRun !== null),
          distinctUntilChanged(),
          switchMap(active => active ? timer(0, FAST_POLL_MS) : EMPTY),
          exhaustMap(() => this.refresh()),
        ),
      )),
    );
  }

  /** Fetches what is new in the conversation. */
  refresh(): Observable<void> {
    const {conversationId, lastSeq} = this.state.getValue();
    if (conversationId === null) {
      return of(undefined);
    }
    return this.api.get(conversationId, lastSeq).pipe(
      tap(response => this.merge(response)),
      map(() => undefined),
      catchError((error: unknown) => {
        if (error instanceof HttpErrorResponse && error.status === 404) {
          this.startNew();
        } else {
          this.fail(error);
        }
        return EMPTY;
      }),
    );
  }

  send(text: string): Observable<void> {
    const page = this.pageContext.current();
    return this.conversationId().pipe(
      switchMap(conversationId => this.api.send(conversationId, text, page).pipe(
        tap(started => this.markRunning(started.runId, 'chat', conversationId)),
      )),
      map(() => undefined),
      catchError((error: unknown) => {
        this.fail(error);
        return EMPTY;
      }),
    );
  }

  approvePlan(planId: number): Observable<void> {
    return this.api.approvePlan(planId).pipe(
      tap(started => this.markRunning(started.runId, 'execute')),
      map(() => undefined),
      catchError((error: unknown) => {
        this.fail(error);
        return EMPTY;
      }),
    );
  }

  /** Ends a proposed plan unrun, then fetches the note the cancelling left. */
  cancelPlan(planId: number): Observable<void> {
    return this.api.cancelPlan(planId).pipe(
      switchMap(() => this.refresh()),
      catchError((error: unknown) => {
        this.fail(error);
        return EMPTY;
      }),
    );
  }

  loadConversations(): Observable<void> {
    return this.api.list().pipe(
      tap(conversations => this.conversations.next(conversations)),
      map(() => undefined),
      catchError((error: unknown) => {
        this.fail(error);
        return EMPTY;
      }),
    );
  }

  /** Opens another conversation; the poll fetches it. */
  select(conversationId: number): void {
    this.state.next(this.emptyState(conversationId));
  }

  delete(conversationId: number): Observable<void> {
    return this.api.delete(conversationId).pipe(
      tap(() => {
        if (this.state.getValue().conversationId === conversationId) {
          this.startNew();
        }
      }),
      switchMap(() => this.loadConversations()),
      catchError((error: unknown) => {
        this.fail(error);
        return EMPTY;
      }),
    );
  }

  stop(): Observable<void> {
    const conversationId = this.state.getValue().conversationId;
    if (conversationId === null) {
      return EMPTY;
    }
    return this.api.stop(conversationId).pipe(
      map(() => undefined),
      catchError((error: unknown) => {
        this.fail(error);
        return EMPTY;
      }),
    );
  }

  startNew(): void {
    this.state.next(this.emptyState(null));
  }

  /**
   * The open conversation, or a new one created for the first message. A new
   * one is not opened here: opening it fetches it, and a fetch sent alongside
   * the message would answer it without the run and stop the polling.
   */
  private conversationId(): Observable<number> {
    const current = this.state.getValue().conversationId;
    if (current !== null) {
      return of(current);
    }
    return this.api.create().pipe(map(conversation => conversation.id));
  }

  private merge(response: AssistantState): void {
    const state = this.state.getValue();
    if (response.conversationId !== state.conversationId) {
      return;
    }
    const known = new Set(state.messages.map(message => message.seq));
    const appended = response.messages.filter(message => !known.has(message.seq));
    const plans: Record<number, AssistantPlan> = {};
    for (const plan of response.plans) {
      plans[plan.id] = plan;
    }
    this.state.next({
      ...state,
      messages: [...state.messages, ...appended],
      lastSeq: Math.max(state.lastSeq, ...appended.map(message => message.seq)),
      plans,
      refs: {...state.refs, ...response.refs},
      activeRun: response.activeRun,
      error: null,
    });
  }

  /** Marks the run; a conversation not yet open is opened with it, so its first fetch already polls. */
  private markRunning(runId: number, type: AssistantRun['type'], conversationId: number | null = null): void {
    const run: AssistantRun = {id: runId, type, status: 'running', steps: 0, error: null, startedAt: Date.now() / 1000, stopRequested: false};
    const state = this.state.getValue();
    const base = conversationId !== null && conversationId !== state.conversationId ? this.emptyState(conversationId) : state;
    this.state.next({...base, activeRun: run, error: null});
  }

  private fail(error: unknown): void {
    const message = error instanceof HttpErrorResponse
      ? (error.error as {errorMessage?: string} | null)?.errorMessage ?? error.message
      : String(error);
    this.state.next({...this.state.getValue(), error: message});
  }

  private emptyState(conversationId: number | null): StoreState {
    return {conversationId, messages: [], plans: {}, refs: {}, activeRun: null, error: null, lastSeq: 0};
  }
}
