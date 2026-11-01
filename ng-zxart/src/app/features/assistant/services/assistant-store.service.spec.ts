import {firstValueFrom, of, throwError} from 'rxjs';
import {HttpErrorResponse} from '@angular/common/http';
import {describe, expect, it, vi} from 'vitest';
import {AssistantDataApiService} from '../../../shared/api/assistant-data-api.service';
import {LocalStorageService} from '../../../shared/services/local-storage.service';
import {AssistantState} from '../../../shared/models/assistant-data';
import {AssistantStore} from './assistant-store.service';
import {AssistantPageContextService} from './assistant-page-context.service';

function storage(initial: Record<string, unknown> = {}): LocalStorageService {
  const values = {...initial};
  return {
    get: (key: string) => (values[key] ?? null),
    set: (key: string, value: unknown) => { values[key] = value; },
    remove: (key: string) => { delete values[key]; },
  } as unknown as LocalStorageService;
}

const pageContext = {current: () => ({url: 'https://zxart.ee/'})} as AssistantPageContextService;

function state(overrides: Partial<AssistantState>): AssistantState {
  return {conversationId: 5, messages: [], plans: [], activeRun: null, refs: {}, ...overrides};
}

function message(seq: number) {
  return {id: seq, seq, role: 'user' as const, kind: 'text' as const, text: `m${seq}`, payload: null, createdAt: 0};
}

describe('AssistantStore', () => {
  it('appends what is new after the last seq and keeps the refs it learnt', async () => {
    const api = {
      get: vi.fn()
        .mockReturnValueOnce(of(state({messages: [message(1)], refs: {'prod:1': {title: 'A', url: '/prod/1'}}})))
        .mockReturnValueOnce(of(state({messages: [message(2)], refs: {'prod:2': {title: 'B', url: '/prod/2'}}}))),
    };
    const store = new AssistantStore(api as unknown as AssistantDataApiService, storage({'assistant-conversation': 5}), pageContext);

    await firstValueFrom(store.refresh());
    await firstValueFrom(store.refresh());

    expect(api.get).toHaveBeenNthCalledWith(1, 5, 0);
    expect(api.get).toHaveBeenNthCalledWith(2, 5, 1);
    const view = await firstValueFrom(store.view$);
    expect(view.messages.map(item => item.segments)).toEqual([[{kind: 'text', text: 'm1'}], [{kind: 'text', text: 'm2'}]]);
  });

  it('creates the conversation on the first message and marks a run as active', async () => {
    const api = {
      create: vi.fn().mockReturnValue(of({id: 8, title: '', createdAt: 0, lastMessageAt: 0})),
      send: vi.fn().mockReturnValue(of({runId: 3})),
    };
    const local = storage();
    const store = new AssistantStore(api as unknown as AssistantDataApiService, local, pageContext);

    await firstValueFrom(store.send('Hello'));

    expect(api.send).toHaveBeenCalledWith(8, 'Hello', {url: 'https://zxart.ee/'});
    expect(local.get('assistant-conversation')).toBe(8);
    expect((await firstValueFrom(store.view$)).running).toBe(true);
  });

  it('shows why a request was refused', async () => {
    const api = {
      send: vi.fn().mockReturnValue(throwError(() => new HttpErrorResponse({status: 409, error: {errorMessage: 'Still working'}}))),
    };
    const store = new AssistantStore(api as unknown as AssistantDataApiService, storage({'assistant-conversation': 5}), pageContext);

    await firstValueFrom(store.send('Hello'), {defaultValue: undefined});

    expect((await firstValueFrom(store.view$)).error).toBe('Still working');
  });

  it('lists the conversations and switches to the one picked', async () => {
    const api = {
      list: vi.fn().mockReturnValue(of([{id: 9, title: 'Old', lastMessageAt: 0, running: false}])),
      get: vi.fn().mockReturnValue(of(state({conversationId: 9, messages: [message(1)]}))),
    };
    const local = storage({'assistant-conversation': 5});
    const store = new AssistantStore(api as unknown as AssistantDataApiService, local, pageContext);

    await firstValueFrom(store.loadConversations());
    expect((await firstValueFrom(store.conversations$)).map(item => item.title)).toEqual(['Old']);

    store.select(9);
    await firstValueFrom(store.refresh());

    expect(local.get('assistant-conversation')).toBe(9);
    expect(api.get).toHaveBeenCalledWith(9, 0);
    expect((await firstValueFrom(store.view$)).messages).toHaveLength(1);
  });

  it('deletes a conversation, forgetting it when it is the open one', async () => {
    const api = {
      delete: vi.fn().mockReturnValue(of([])),
      list: vi.fn().mockReturnValue(of([])),
    };
    const local = storage({'assistant-conversation': 5});
    const store = new AssistantStore(api as unknown as AssistantDataApiService, local, pageContext);

    await firstValueFrom(store.delete(5));

    expect(api.delete).toHaveBeenCalledWith(5);
    expect(local.get('assistant-conversation')).toBeNull();
    expect(await firstValueFrom(store.conversations$)).toEqual([]);
  });

  it('asks the open conversation to stop', async () => {
    const api = {stop: vi.fn().mockReturnValue(of([]))};
    const store = new AssistantStore(api as unknown as AssistantDataApiService, storage({'assistant-conversation': 5}), pageContext);

    await firstValueFrom(store.stop(), {defaultValue: undefined});

    expect(api.stop).toHaveBeenCalledWith(5);
  });

  it('fetches an idle conversation once and then waits for the user', () => {
    vi.useFakeTimers();
    const api = {get: vi.fn().mockReturnValue(of(state({messages: [message(1)]})))};
    const store = new AssistantStore(api as unknown as AssistantDataApiService, storage({'assistant-conversation': 5}), pageContext);

    const subscription = store.polling$.subscribe();
    vi.advanceTimersByTime(60000);
    subscription.unsubscribe();
    vi.useRealTimers();

    expect(api.get).toHaveBeenCalledTimes(1);
  });

  it('polls while a run is active and stops once it is over', () => {
    vi.useFakeTimers();
    const run = {id: 3, type: 'chat' as const, status: 'running' as const, steps: 0, error: null, startedAt: 0, stopRequested: false};
    const api = {
      get: vi.fn()
        .mockReturnValueOnce(of(state({activeRun: run})))
        .mockReturnValueOnce(of(state({activeRun: run})))
        .mockReturnValue(of(state({messages: [message(1)]}))),
    };
    const store = new AssistantStore(api as unknown as AssistantDataApiService, storage({'assistant-conversation': 5}), pageContext);

    const subscription = store.polling$.subscribe();
    vi.advanceTimersByTime(60000);
    subscription.unsubscribe();
    vi.useRealTimers();

    expect(api.get).toHaveBeenCalledTimes(3);
  });

  it('starts over without a request when a new conversation is asked for', async () => {
    const local = storage({'assistant-conversation': 5});
    const store = new AssistantStore({} as AssistantDataApiService, local, pageContext);

    store.startNew();

    expect(local.get('assistant-conversation')).toBeNull();
    expect((await firstValueFrom(store.view$)).hasConversation).toBe(false);
  });
});
