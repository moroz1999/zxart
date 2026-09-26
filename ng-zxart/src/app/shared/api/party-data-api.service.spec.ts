import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {PartyDataApiService} from './party-data-api.service';

describe('PartyDataApiService', () => {
  it('delete posts the id and the `delete` action to /party-data/', async () => {
    const http = {post: vi.fn().mockReturnValue(of({id: 900}))};
    const service = new PartyDataApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.delete(42));

    expect(http.post).toHaveBeenCalledWith('/party-data/', null, {params: {id: 42, action: 'delete'}});
    expect(result).toEqual({id: 900});
  });
});
