import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of, throwError} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {Tag} from '../models/tag';
import {TagLookupApiService} from './tag-lookup-api.service';

describe('TagLookupApiService', () => {
  const games = {id: 5, url: '/tag/5', title: 'Games, Gaming (Software to play)', value: 'Games', synonym: 'Gaming', description: 'Software to play'};

  it('asks /tag-lookup/ for the trimmed phrase and answers the tags', async () => {
    const http = {get: vi.fn().mockReturnValue(of({items: [games]}))};
    const service = new TagLookupApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.lookup(' game '));

    expect(http.get).toHaveBeenCalledWith('/tag-lookup/', {params: {query: 'game'}});
    expect(result).toEqual([new Tag(games)]);
  });

  it('answers nothing for a blank phrase without asking', async () => {
    const http = {get: vi.fn()};
    const service = new TagLookupApiService(http as unknown as HttpClient);

    expect(await firstValueFrom(service.lookup('  '))).toEqual([]);
    expect(http.get).not.toHaveBeenCalled();
  });

  it('answers nothing when the request fails', async () => {
    const http = {get: vi.fn().mockReturnValue(throwError(() => new Error('offline')))};
    const service = new TagLookupApiService(http as unknown as HttpClient);

    expect(await firstValueFrom(service.lookup('game'))).toEqual([]);
  });
});
