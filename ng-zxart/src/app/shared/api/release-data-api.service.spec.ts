import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {ReleaseDataApiService} from './release-data-api.service';
import {readSaveBody} from './read-save-body.testing';

describe('ReleaseDataApiService', () => {
  it('delete posts the id and the `delete` action to /release-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new ReleaseDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/release-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });

  it('clone posts the id and the `clone` action to /release-data/ and answers the copy', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 901}))};
    const service = new ReleaseDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.clone(42));

    expect(http.post).toHaveBeenCalledWith('/release-data/', null, {params: {id: 42, action: 'clone'}});
    expect(result).toEqual({id: 901});
  });

  it('deleteMember posts the id, the author and the `deleteMember` action to /release-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new ReleaseDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.deleteMember(42, 7));

    expect(http.post).toHaveBeenCalledWith('/release-data/', null, {params: {id: 42, action: 'deleteMember', authorId: 7}});
    expect(result).toEqual({id: 42});
  });

  it('deleteFile posts the id, the file and the `deleteFile` action to /release-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new ReleaseDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.deleteFile(42, 7));

    expect(http.post).toHaveBeenCalledWith('/release-data/', null, {params: {id: 42, action: 'deleteFile', fileId: 7}});
    expect(result).toEqual({id: 42});
  });

  it('update posts the id, the request, the release file and the files by kind to /release-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 42}))};
    const service = new ReleaseDataApiService(http as unknown as HttpClient);
    const request = {fields: {version: '1.1'}, importOrigins: [{origin: 'zxdb', importId: '6001'}]};
    const tape = new File(['t'], 'alpha.tap');
    const inlay = new File(['i'], 'inlay.jpg');

    await firstValueFrom(service.update(42, request, {file: tape, files: {inlays: [inlay]}}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/release-data/');
    expect(options).toEqual({params: {id: 42, action: 'update'}});
    const {data, files} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(files.get('file')).toEqual(tape);
    expect(files.getAll('files[inlays][]')).toEqual([inlay]);
  });

  it('createFromFiles posts the request and the release files to /release-data/ and answers the ids', async () => {
    const http = {post: vi.fn().mockReturnValue(of({ids: [900, 901]}))};
    const service = new ReleaseDataApiService(http as unknown as HttpClient);
    const request = {fields: {title: 'Goldmaster Tape', prodId: 7}, authors: []};
    const alpha = new File(['a'], 'alpha.tap');
    const beta = new File(['b'], 'beta.tap');

    const result = await firstValueFrom(service.createFromFiles(request, {releaseFiles: [alpha, beta]}));

    const [url, body, options] = http.post.mock.calls[0];
    expect(url).toBe('/release-data/');
    expect(options).toEqual({params: {action: 'createFromFiles'}});
    const {data, files} = await readSaveBody(body);
    expect(data).toEqual(request);
    expect(files.getAll('releaseFiles[]')).toEqual([alpha, beta]);
    expect(result).toEqual({ids: [900, 901]});
  });
});
