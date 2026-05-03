import {Injectable} from '@angular/core';
import {BehaviorSubject, defer, Observable} from 'rxjs';
import {finalize} from 'rxjs/operators';
import {ZxProd} from '../../../shared/models/zx-prod';
import {ProdRelatedProdsApiService} from './prod-related-prods-api.service';

type RelatedProdsKind = 'compilationItems' | 'compilations' | 'seriesProds' | 'series';

@Injectable({providedIn: 'root'})
export class ProdRelatedProdsService {
  private readonly prodsStores = new Map<string, BehaviorSubject<ZxProd[] | null>>();
  private readonly loadingKeys = new Set<string>();

  constructor(private readonly api: ProdRelatedProdsApiService) {}

  getCompilationItems(elementId: number): Observable<ZxProd[] | null> {
    return this.getProds(elementId, 'compilationItems');
  }

  getCompilations(elementId: number): Observable<ZxProd[] | null> {
    return this.getProds(elementId, 'compilations');
  }

  getSeriesProds(elementId: number): Observable<ZxProd[] | null> {
    return this.getProds(elementId, 'seriesProds');
  }

  getSeries(elementId: number): Observable<ZxProd[] | null> {
    return this.getProds(elementId, 'series');
  }

  private getProds(elementId: number, kind: RelatedProdsKind): Observable<ZxProd[] | null> {
    return defer(() => {
      const store = this.getProdsStore(elementId, kind);
      const key = this.prodsKey(elementId, kind);
      if (store.getValue() === null && !this.loadingKeys.has(key)) {
        this.loadingKeys.add(key);
        this.loadProds(elementId, kind).pipe(
          finalize(() => this.loadingKeys.delete(key)),
        ).subscribe(prods => store.next(prods));
      }
      return store.asObservable();
    });
  }

  private loadProds(elementId: number, kind: RelatedProdsKind): Observable<ZxProd[]> {
    switch (kind) {
      case 'compilationItems':
        return this.api.getCompilationItems(elementId);
      case 'compilations':
        return this.api.getCompilations(elementId);
      case 'seriesProds':
        return this.api.getSeriesProds(elementId);
      case 'series':
        return this.api.getSeries(elementId);
    }
  }

  private getProdsStore(elementId: number, kind: RelatedProdsKind): BehaviorSubject<ZxProd[] | null> {
    const key = this.prodsKey(elementId, kind);
    let store = this.prodsStores.get(key);
    if (!store) {
      store = new BehaviorSubject<ZxProd[] | null>(null);
      this.prodsStores.set(key, store);
    }
    return store;
  }

  private prodsKey(elementId: number, kind: RelatedProdsKind): string {
    return `${kind}:${elementId}`;
  }
}
