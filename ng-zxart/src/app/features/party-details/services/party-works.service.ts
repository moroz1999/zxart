import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {ZxProd} from '../../../shared/models/zx-prod';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {PartyOverview} from '../models/party-overview.dto';
import {PartyProdsApiService} from './party-prods-api.service';
import {PartyPicturesApiService} from './party-pictures-api.service';
import {PartyMusicApiService} from './party-music-api.service';
import {PartyOverviewApiService} from './party-overview-api.service';

/**
 * Owns the party page's work data. The heavy per-compo media (compos tab) is fetched lazily, one
 * compo at a time, and cached per (partyId, compoType). The Overview tab loads its own dedicated
 * aggregate endpoint once. Every stream is shared so repeat subscriptions reuse the cached result.
 */
@Injectable({providedIn: 'root'})
export class PartyWorksService {
  private readonly overview = new Map<number, Observable<PartyOverview>>();
  private readonly prods = new Map<string, Observable<ZxProd[]>>();
  private readonly pictures = new Map<string, Observable<ZxPictureDto[]>>();
  private readonly tunes = new Map<string, Observable<ZxTuneDto[]>>();

  constructor(
    private readonly prodsApi: PartyProdsApiService,
    private readonly picturesApi: PartyPicturesApiService,
    private readonly musicApi: PartyMusicApiService,
    private readonly overviewApi: PartyOverviewApiService,
  ) {}

  getOverview(partyId: number): Observable<PartyOverview> {
    return this.cached(this.overview, partyId, () => this.overviewApi.getOverview(partyId));
  }

  getCompoProds(partyId: number, compoType: string): Observable<ZxProd[]> {
    return this.cached(this.prods, PartyWorksService.key(partyId, compoType), () => this.prodsApi.getProds(partyId, compoType));
  }

  getCompoPictures(partyId: number, compoType: string): Observable<ZxPictureDto[]> {
    return this.cached(this.pictures, PartyWorksService.key(partyId, compoType), () => this.picturesApi.getPictures(partyId, compoType));
  }

  getCompoTunes(partyId: number, compoType: string): Observable<ZxTuneDto[]> {
    return this.cached(this.tunes, PartyWorksService.key(partyId, compoType), () => this.musicApi.getTunes(partyId, compoType));
  }

  private static key(partyId: number, compoType: string): string {
    return `${partyId}:${compoType}`;
  }

  private cached<K, T>(store: Map<K, Observable<T>>, key: K, factory: () => Observable<T>): Observable<T> {
    const existing = store.get(key);
    if (existing) {
      return existing;
    }
    const stream = factory().pipe(shareReplay({bufferSize: 1, refCount: false}));
    store.set(key, stream);
    return stream;
  }
}
