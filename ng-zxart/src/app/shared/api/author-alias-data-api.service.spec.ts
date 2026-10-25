import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {AuthorAliasDataApiService} from './author-alias-data-api.service';
import {readSaveBody} from './read-save-body.testing';

describe('AuthorAliasDataApiService', () => {
  it('delete posts the id and the `delete` action to /author-alias-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new AuthorAliasDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/author-alias-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });

  it('convertToAuthor posts the id and the `convertToAuthor` action to /author-alias-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new AuthorAliasDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.convertToAuthor(42));

    expect(http.post).toHaveBeenCalledWith('/author-alias-data/', null, {params: {id: 42, action: 'convertToAuthor'}});
    expect(result).toEqual({id: 900});
  });

  it('create posts the request and its files to /author-alias-data/ as `create`', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new AuthorAliasDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Wandering Goldmaster', authorId: 4}, prods: [{elementId: 20, roles: ['music'], startDate: 0, endDate: 0}]};

    const result = await firstValueFrom(service.create(request));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/author-alias-data/');
    expect(options).toEqual({params: {action: 'create'}});
    const {data} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(result).toEqual({id: 900});
  });

  it('update posts the id, the request and `update` to /author-alias-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new AuthorAliasDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Zeta'}, groups: []};

    await firstValueFrom(service.update(42, request));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/author-alias-data/');
    expect(options).toEqual({params: {id: 42, action: 'update'}});
    expect((await readSaveBody(body)).data).toEqual(request);
  });
});
