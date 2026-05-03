import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map, shareReplay} from 'rxjs/operators';
import {ProdSeriesEntryDto, ProdSeriesPayload, ProdSummariesPayload, ProdSummaryDto} from '../models/prod-summary.dto';
import {ZxProd} from '../../../shared/models/zx-prod';
import {LegalStatus, ZxProdDto} from '../../../shared/models/zx-prod-dto';

export interface ProdSeriesEntry {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly prods: ZxProd[];
}

@Injectable({providedIn: 'root'})
export class ProdRelatedProdsApiService {
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

  getSeries(elementId: number): Observable<ProdSeriesEntry[]> {
    const params = new HttpParams().set('id', String(elementId));
    return this.http.get<ProdSeriesPayload>('/prod-series/', {params}).pipe(
      map(response => (response.series ?? []).map(entry => this.toSeriesEntry(entry))),
      catchError(() => of([])),
      shareReplay({bufferSize: 1, refCount: false}),
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

  private toSeriesEntry(entry: ProdSeriesEntryDto): ProdSeriesEntry {
    return {
      id: entry.id,
      title: entry.title,
      url: entry.url,
      prods: entry.prods.map(prod => this.toZxProd(prod)),
    };
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
