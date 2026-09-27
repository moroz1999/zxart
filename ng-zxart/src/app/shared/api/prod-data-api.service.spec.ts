import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {ProdDataApiService} from './prod-data-api.service';

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
});
