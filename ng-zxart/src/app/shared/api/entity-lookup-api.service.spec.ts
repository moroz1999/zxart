import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of, throwError} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {EntityLookupApiService} from './entity-lookup-api.service';

describe('EntityLookupApiService', () => {
  it('asks /entity-lookup/ for the types and the trimmed phrase and answers the items', async () => {
    const http = {get: vi.fn().mockReturnValue(of({items: [{id: 7, title: 'Rebelstar (1988)'}]}))};
    const service = new EntityLookupApiService(http as unknown as HttpClient);

    const result = await firstValueFrom(service.lookup('zxProd,zxRelease', '  rebel-star '));

    expect(http.get).toHaveBeenCalledWith('/entity-lookup/', {params: {types: 'zxProd,zxRelease', query: 'rebel-star'}});
    expect(result).toEqual([{id: 7, title: 'Rebelstar (1988)'}]);
  });

  it('answers nothing for a blank phrase without asking', async () => {
    const http = {get: vi.fn()};
    const service = new EntityLookupApiService(http as unknown as HttpClient);

    expect(await firstValueFrom(service.lookup('author', '   '))).toEqual([]);
    expect(http.get).not.toHaveBeenCalled();
  });

  it('answers nothing when the request fails', async () => {
    const http = {get: vi.fn().mockReturnValue(throwError(() => new Error('offline')))};
    const service = new EntityLookupApiService(http as unknown as HttpClient);

    expect(await firstValueFrom(service.lookup('country', 'pol'))).toEqual([]);
  });
});
