import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';

/** Raw shape returned by the /prodlist/ REST endpoint. */
interface ProdRestItem {
  readonly id: number;
  readonly url: string;
  readonly structureType: string;
  readonly dateCreated: number;
  readonly title: string;
  readonly year: number | null;
  readonly listImagesUrls: string[];
  readonly votes: number;
  readonly votesAmount: number;
  readonly userVote: number | null;
  readonly denyVoting: boolean;
  readonly hardwareInfo: Array<{id: string; title: string}>;
  readonly authorsInfoShort: Array<{title: string; url: string; roles: string[]}>;
  readonly categoriesInfo: Array<{id: number; title: string; url: string}>;
  readonly partyInfo: {id: number; title: string; url: string} | null;
  readonly partyPlace: number;
  readonly legalStatus: string | null;
  readonly languagesInfo: Array<{id: string; title: string; url: string | null}>;
  readonly groupsInfo: Array<{id: number; title: string; url: string}>;
  readonly youtubeId: string | null;
  readonly releaseType: string | null;
}

interface ProdRestResponse {
  total: number;
  items: ProdRestItem[];
}

export interface PaginatedProdsResponse {
  total: number;
  items: ZxProdDto[];
}

@Injectable({
  providedIn: 'root',
})
export class ProdsBrowserService {
  constructor(private http: HttpClient) {}

  getPaged(elementId: number, start: number, limit: number, sorting: string): Observable<PaginatedProdsResponse> {
    return this.http.get<ProdRestResponse>('/prodlist/', {
      params: {elementId: String(elementId), start: String(start), limit: String(limit), sorting},
    }).pipe(
      map(response => ({
        total: response.total,
        items: response.items.map(item => this.mapToDto(item)),
      })),
      catchError(() => of({total: 0, items: []}))
    );
  }

  private mapToDto(item: ProdRestItem): ZxProdDto {
    return {
      id: item.id,
      url: item.url,
      title: item.title,
      structureType: item.structureType as 'zxProd' | 'zxRelease',
      dateCreated: item.dateCreated,
      year: item.year != null ? String(item.year) : undefined,
      youtubeId: item.youtubeId ?? undefined,
      listImagesUrls: item.listImagesUrls,
      hardwareInfo: item.hardwareInfo,
      authorsInfoShort: item.authorsInfoShort,
      categoriesInfo: item.categoriesInfo,
      partyInfo: item.partyInfo ?? undefined,
      partyPlace: item.partyPlace,
      legalStatus: (item.legalStatus as any) ?? undefined,
      languagesInfo: item.languagesInfo,
      groupsInfo: item.groupsInfo,
      releaseType: item.releaseType ?? undefined,
      votes: item.votes,
      votesAmount: item.votesAmount,
      userVote: item.userVote ?? 0,
      denyVoting: item.denyVoting,
    };
  }
}
