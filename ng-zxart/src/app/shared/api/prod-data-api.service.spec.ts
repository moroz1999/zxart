import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {ProdDataApiService} from './prod-data-api.service';
import {readSaveBody} from './read-save-body.testing';

describe('ProdDataApiService', () => {
  it('delete posts the id and the `delete` action to /prod-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new ProdDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/prod-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });

  it('deleteMember posts the id, the author and the `deleteMember` action to /prod-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new ProdDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.deleteMember(42, 7));

    expect(http.post).toHaveBeenCalledWith('/prod-data/', null, {params: {id: 42, action: 'deleteMember', authorId: 7}});
    expect(result).toEqual({id: 42});
  });

  it('deleteFile posts the id, the file and the `deleteFile` action to /prod-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new ProdDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.deleteFile(42, 7));

    expect(http.post).toHaveBeenCalledWith('/prod-data/', null, {params: {id: 42, action: 'deleteFile', fileId: 7}});
    expect(result).toEqual({id: 42});
  });

  it('update posts the id, the request and the files by kind to /prod-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new ProdDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Goldmaster Demo'}, authors: [{authorId: 4, roles: ['code'], startDate: 0, endDate: 0}]};
    const screen = new File(['s'], 'screen.png');

    await firstValueFrom(service.update(42, request, {files: {screenshots: [screen]}}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/prod-data/');
    expect(options).toEqual({params: {id: 42, action: 'update'}});
    const {data, files} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(files.getAll('files[screenshots][]')).toEqual([screen]);
  });

  it('batchUpload posts the request, the release files and the files to /prod-data/ and answers the ids', async () => {
    const http = {post: vi.fn().mockReturnValue(of({ids: [900, 901]}))};
    const service = new ProdDataApiService(http as unknown as HttpClient);
    const request = {fields: {year: 1999}, authors: []};
    const alpha = new File(['a'], 'alpha.tap');
    const beta = new File(['b'], 'beta.tap');
    const map = new File(['m'], 'map.png');

    const result = await firstValueFrom(service.batchUpload(request, {releaseFiles: [alpha, beta], files: {maps: [map]}}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/prod-data/');
    expect(options).toEqual({params: {action: 'batchUpload'}});
    const {data, files} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(files.getAll('releaseFiles[]')).toEqual([alpha, beta]);
    expect(files.getAll('files[maps][]')).toEqual([map]);
    expect(result).toEqual({ids: [900, 901]});
  });
});
