import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {AssistantDataApiService} from './assistant-data-api.service';

describe('AssistantDataApiService', () => {
  it('create posts the `create` action to /assistant-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 5}))};
    const service = new AssistantDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.create());

    expect(http.post).toHaveBeenCalledWith('/assistant-data/', null, {params: {action: 'create'}});
    expect(result).toEqual({id: 5});
  });

  it('get asks for what came after the seq', async () => {
    const http = {get: vi.fn().mockReturnValue(of({conversationId: 5}))};
    const service = new AssistantDataApiService(http as unknown as HttpClient);

    await firstValueFrom(service.get(5, 12));

    expect(http.get).toHaveBeenCalledWith('/assistant-data/', {params: {action: 'get', id: 5, afterSeq: 12}});
  });

  it('send posts the text and the page', async () => {
    const http = {post: vi.fn().mockReturnValue(of({runId: 9}))};
    const service = new AssistantDataApiService(http as unknown as HttpClient);
    const page = {url: 'https://zxart.ee/prod/7', entityType: 'prod', entityId: 7};

    const result = await firstValueFrom(service.send(5, 'Fix the year', page));

    expect(http.post).toHaveBeenCalledWith('/assistant-data/', {conversationId: 5, text: 'Fix the year', page}, {params: {action: 'send'}});
    expect(result).toEqual({runId: 9});
  });

  it('list asks for the conversations', async () => {
    const http = {get: vi.fn().mockReturnValue(of([]))};
    const service = new AssistantDataApiService(http as unknown as HttpClient);

    await firstValueFrom(service.list());

    expect(http.get).toHaveBeenCalledWith('/assistant-data/', {params: {action: 'list'}});
  });

  it('delete and stop post the conversation', async () => {
    const http = {post: vi.fn().mockReturnValue(of([]))};
    const service = new AssistantDataApiService(http as unknown as HttpClient);

    await firstValueFrom(service.delete(5));
    await firstValueFrom(service.stop(6));

    expect(http.post).toHaveBeenNthCalledWith(1, '/assistant-data/', {conversationId: 5}, {params: {action: 'delete'}});
    expect(http.post).toHaveBeenNthCalledWith(2, '/assistant-data/', {conversationId: 6}, {params: {action: 'stop'}});
  });

  it('approvePlan posts the plan', async () => {
    const http = {post: vi.fn().mockReturnValue(of({runId: 10}))};
    const service = new AssistantDataApiService(http as unknown as HttpClient);

    await firstValueFrom(service.approvePlan(3));

    expect(http.post).toHaveBeenCalledWith('/assistant-data/', {planId: 3}, {params: {action: 'approvePlan'}});
  });
});
