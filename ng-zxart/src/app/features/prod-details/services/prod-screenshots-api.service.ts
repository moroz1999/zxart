import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {EntityPrefetchService} from '../../../shared/services/entity-prefetch.service';
import {ProdFileDto, ProdFilesPayload} from '../models/prod-file.dto';

@Injectable({providedIn: 'root'})
export class ProdScreenshotsApiService {
  constructor(private readonly prefetch: EntityPrefetchService) {}

  /**
   * The screenshots carry the prod page's LCP image, and the prod route
   * resolves this request while the page's chunk is still downloading, so the
   * section usually collects a response that is already on its way.
   */
  getScreenshots(elementId: number): Observable<ProdFileDto[]> {
    return this.prefetch.get<ProdFilesPayload>('/prod-screenshots/', {id: elementId}).pipe(
      map(response => response.files ?? []),
      catchError(() => of([])),
    );
  }
}
