import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map, shareReplay} from 'rxjs/operators';
import {ProdSummariesPayload, ProdSummaryDto} from '../models/prod-summary.dto';
import {ZxProd} from '../../../shared/models/zx-prod';
import {LegalStatus, ZxProdDto} from '../../../shared/models/zx-prod-dto';

@Injectable({providedIn: 'root'})
export class ProdRelatedProdsApiService {
  private readonly seriesPayloadRequests = new Map<number, Observable<ProdSummariesPayload>>();

  constructor(private readonly http: HttpClient) {}

  getCompilationItems(elementId: number): Observable<ZxProd[]> {
    return this.getProds('/prod-compilation-items/', elementId);
  }

  getSeriesProds(elementId: number): Observable<ZxProd[]> {
    return this.getProds('/prod-series-prods/', elementId);
  }

  getCompilations(elementId: number): Observable<ZxProd[]> {
    return this.getProds('/prod-compilations/', elementId);
  }

  getSeries(elementId: number): Observable<ZxProd[]> {
    return this.getSeriesPayload(elementId).pipe(
      map(response => (response.prods ?? []).map(prod => this.toZxProd(prod))),
    );
  }

  getSeriesUrl(elementId: number): Observable<string | null> {
    return this.getSeriesPayload(elementId).pipe(
      map(response => response.seriesUrl ?? null),
    );
  }

  private getProds(url: string, elementId: number): Observable<ZxProd[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdSummariesPayload>(url, {params}).pipe(
      map(response => (response.prods ?? []).map(prod => this.toZxProd(prod))),
      catchError(() => of([])),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private getSeriesPayload(elementId: number): Observable<ProdSummariesPayload> {
    const existingRequest = this.seriesPayloadRequests.get(elementId);
    if (existingRequest) {
      return existingRequest;
    }

    const params = new HttpParams().set('id', String(elementId));
    const request = this.http.get<ProdSummariesPayload>('/prod-series/', {params}).pipe(
      catchError(() => of({prods: [], seriesUrl: null})),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.seriesPayloadRequests.set(elementId, request);
    return request;
  }

  toZxProd(summary: ProdSummaryDto): ZxProd {
    const dto: ZxProdDto = {
      id: summary.id,
      title: summary.title,
      url: summary.url,
      structureType: 'zxProd',
      dateCreated: 0,
      year: String(summary.year || ''),
      listImagesUrls: summary.imageUrl ? [summary.imageUrl] : [],
      hardwareInfo: [],
      groupsInfo: [],
      publishersInfo: [],
      authorsInfoShort: [],
      categoriesInfo: [],
      languagesInfo: [],
      votes: summary.votes,
      votesAmount: summary.votesAmount,
      userVote: 0,
      denyVoting: false,
      legalStatus: summary.legalStatus as LegalStatus,
      externalLink: '',
    };

    return new ZxProd(dto);
  }
}
