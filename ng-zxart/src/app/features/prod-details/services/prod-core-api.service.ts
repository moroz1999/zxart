import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {EntityPrefetchService} from '../../../shared/services/entity-prefetch.service';
import {ProdCoreDto} from '../models/prod-core.dto';

@Injectable({providedIn: 'root'})
export class ProdCoreApiService {
  constructor(private readonly prefetch: EntityPrefetchService) {}

  /**
   * The prod route resolves this request while the page's chunk is still
   * downloading, so this usually collects a response that is already on its way.
   */
  getCore(elementId: number): Observable<ProdCoreDto | null> {
    return this.prefetch.get<ProdCoreDto>('/prod-details/', {id: elementId}).pipe(
      catchError(() => of(null)),
    );
  }
}
