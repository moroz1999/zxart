import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {GroupDataApiService} from './group-data-api.service';

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
});
