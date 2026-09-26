import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {AuthorDataApiService} from './author-data-api.service';

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
});
