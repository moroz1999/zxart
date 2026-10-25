import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {GroupAliasDataApiService} from './group-alias-data-api.service';
import {readSaveBody} from './read-save-body.testing';

describe('GroupAliasDataApiService', () => {
  it('delete posts the id and the `delete` action to /group-alias-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new GroupAliasDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/group-alias-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });

  it('convertToGroup posts the id and the `convertToGroup` action to /group-alias-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new GroupAliasDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.convertToGroup(42));

    expect(http.post).toHaveBeenCalledWith('/group-alias-data/', null, {params: {id: 42, action: 'convertToGroup'}});
    expect(result).toEqual({id: 900});
  });

  it('create posts the request and its files to /group-alias-data/ as `create`', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new GroupAliasDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Goldmaster Crew', groupId: 5}, members: []};

    const result = await firstValueFrom(service.create(request));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/group-alias-data/');
    expect(options).toEqual({params: {action: 'create'}});
    const {data} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(result).toEqual({id: 900});
  });

  it('update posts the id, the request and `update` to /group-alias-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new GroupAliasDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Zeta Crew'}, importOrigins: []};

    await firstValueFrom(service.update(42, request));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/group-alias-data/');
    expect(options).toEqual({params: {id: 42, action: 'update'}});
    expect((await readSaveBody(body)).data).toEqual(request);
  });
});
