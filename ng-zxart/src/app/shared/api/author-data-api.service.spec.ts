import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {AuthorDataApiService} from './author-data-api.service';
import {readSaveBody} from './read-save-body.testing';

describe('AuthorDataApiService', () => {
  it('delete posts the id and the `delete` action to /author-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new AuthorDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/author-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });

  it('convertToGroup posts the id and the `convertToGroup` action to /author-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new AuthorDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.convertToGroup(42));

    expect(http.post).toHaveBeenCalledWith('/author-data/', null, {params: {id: 42, action: 'convertToGroup'}});
    expect(result).toEqual({id: 900});
  });

  it('create posts the request and its files to /author-data/ as `create`', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new AuthorDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Goldmaster Writer'}, realNames: {1: 'Johann'}, groups: [{elementId: 10, roles: ['coder'], startDate: 0, endDate: 0}]};
    const logo = new File(['png'], 'logo.png');

    const result = await firstValueFrom(service.create(request, {image: logo}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/author-data/');
    expect(options).toEqual({params: {action: 'create'}});
    const {data, files} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(files.get('image')).toEqual(logo);
    expect(result).toEqual({id: 900});
  });

  it('update posts the id, the request and `update` to /author-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new AuthorDataApiService(http as unknown as HttpClient);
    const request = {removeImage: true, fields: {}};

    await firstValueFrom(service.update(42, request, {}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/author-data/');
    expect(options).toEqual({params: {id: 42, action: 'update'}});
    expect((await readSaveBody(body)).data).toEqual(request);
  });
});
