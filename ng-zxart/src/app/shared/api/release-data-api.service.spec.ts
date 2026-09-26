import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {ReleaseDataApiService} from './release-data-api.service';

describe('ReleaseDataApiService', () => {
  it('delete posts the id and the `delete` action to /release-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new ReleaseDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/release-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });
});
