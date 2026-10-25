import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {GroupDataApiService} from './group-data-api.service';
import {readSaveBody} from './read-save-body.testing';

describe('GroupDataApiService', () => {
  it('delete posts the id and the `delete` action to /group-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new GroupDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/group-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });

  it('convertToAuthor posts the id and the `convertToAuthor` action to /group-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new GroupDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.convertToAuthor(42));

    expect(http.post).toHaveBeenCalledWith('/group-data/', null, {params: {id: 42, action: 'convertToAuthor'}});
    expect(result).toEqual({id: 900});
  });

  it('deleteMember posts the id, the author and the `deleteMember` action to /group-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new GroupDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.deleteMember(42, 7));

    expect(http.post).toHaveBeenCalledWith('/group-data/', null, {params: {id: 42, action: 'deleteMember', authorId: 7}});
    expect(result).toEqual({id: 42});
  });

  it('create posts the request and its files to /group-data/ as `create`', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new GroupDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Goldmaster Guild'}, members: [{authorId: 4, roles: ['coder'], startDate: 0, endDate: 0}]};
    const logo = new File(['png'], 'logo.png');

    const result = await firstValueFrom(service.create(request, {image: logo}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/group-data/');
    expect(options).toEqual({params: {action: 'create'}});
    const {data, files} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(files.get('image')).toEqual(logo);
    expect(result).toEqual({id: 900});
  });

  it('update posts the id, the request and `update` to /group-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new GroupDataApiService(http as unknown as HttpClient);
    const request = {fields: {}, removeImage: true};

    await firstValueFrom(service.update(42, request, {}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/group-data/');
    expect(options).toEqual({params: {id: 42, action: 'update'}});
    expect((await readSaveBody(body)).data).toEqual(request);
  });
});
