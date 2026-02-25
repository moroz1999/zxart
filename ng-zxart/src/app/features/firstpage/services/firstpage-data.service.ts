import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {FirstpageProdDto} from '../../../shared/models/firstpage-prod-dto';
import {PartyDto} from '../../../shared/models/party-dto';
import {ZxProd} from '../../../shared/models/zx-prod';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';

@Injectable({
  providedIn: 'root'
})
export class FirstpageDataService {
  constructor(private http: HttpClient) {}

  getNewProds(limit: number, minRating: number): Observable<ZxProd[]> {
    return this.get<FirstpageProdDto[]>('newProds', {limit, minRating}).pipe(
      map(dtos => dtos.map(dto => this.toZxProd(dto)))
    );
  }

  getNewPictures(limit: number): Observable<ZxPictureDto[]> {
    return this.get<ZxPictureDto[]>('newPictures', {limit});
  }

  getNewTunes(limit: number): Observable<ZxTuneDto[]> {
    return this.get<ZxTuneDto[]>('newTunes', {limit});
  }

  getBestNewDemos(limit: number, minRating: number): Observable<ZxProd[]> {
    return this.get<FirstpageProdDto[]>('bestNewDemos', {limit, minRating}).pipe(
      map(dtos => dtos.map(dto => this.toZxProd(dto)))
    );
  }

  getBestNewGames(limit: number, minRating: number): Observable<ZxProd[]> {
    return this.get<FirstpageProdDto[]>('bestNewGames', {limit, minRating}).pipe(
      map(dtos => dtos.map(dto => this.toZxProd(dto)))
    );
  }

  getRecentParties(limit: number): Observable<PartyDto[]> {
    return this.get<PartyDto[]>('recentParties', {limit});
  }

  getBestPicturesOfMonth(limit: number): Observable<ZxPictureDto[]> {
    return this.get<ZxPictureDto[]>('bestPicturesOfMonth', {limit});
  }

  getLatestAddedProds(limit: number): Observable<ZxProd[]> {
    return this.get<FirstpageProdDto[]>('latestAddedProds', {limit}).pipe(
      map(dtos => dtos.map(dto => this.toZxProd(dto)))
    );
  }

  getLatestAddedReleases(limit: number): Observable<ZxProd[]> {
    return this.get<FirstpageProdDto[]>('latestAddedReleases', {limit}).pipe(
      map(dtos => dtos.map(dto => this.toZxProd(dto, 'zxRelease')))
    );
  }

  getSupportProds(limit: number): Observable<ZxProd[]> {
    return this.get<FirstpageProdDto[]>('supportProds', {limit}).pipe(
      map(dtos => dtos.map(dto => this.toZxProd(dto)))
    );
  }

  getUnvotedPictures(limit: number): Observable<ZxPictureDto[]> {
    return this.get<ZxPictureDto[]>('unvotedPictures', {limit});
  }

  getRandomGoodPictures(limit: number): Observable<ZxPictureDto[]> {
    return this.get<ZxPictureDto[]>('randomGoodPictures', {limit});
  }

  getUnvotedTunes(limit: number): Observable<ZxTuneDto[]> {
    return this.get<ZxTuneDto[]>('unvotedTunes', {limit});
  }

  getRandomGoodTunes(limit: number): Observable<ZxTuneDto[]> {
    return this.get<ZxTuneDto[]>('randomGoodTunes', {limit});
  }

  private toZxProd(dto: FirstpageProdDto, structureType: 'zxProd' | 'zxRelease' = 'zxProd'): ZxProd {
    const prodDto: ZxProdDto = {
      id: dto.id,
      url: dto.url,
      title: dto.title,
      structureType,
      dateCreated: 0,
      year: dto.year ?? undefined,
      hardwareInfo: dto.hardwareInfo ?? undefined,
      votes: dto.votes,
      userVote: dto.userVote ?? 0,
      denyVoting: dto.denyVoting,
      legalStatus: dto.legalStatus ?? undefined,
      listImagesUrls: dto.imageUrl ? [dto.imageUrl] : [],
      authorsInfoShort: dto.authors.map(a => ({title: a.name, url: a.url, roles: []})),
      partyInfo: dto.party ? {id: 0, title: dto.party.title, url: dto.party.url} : undefined,
      partyPlace: dto.party?.place ?? 0,
    };
    return new ZxProd(prodDto);
  }

  private get<T>(action: string, params: Record<string, number>): Observable<T> {
    const queryParams: Record<string, string> = {action};
    for (const [key, value] of Object.entries(params)) {
      queryParams[key] = String(value);
    }
    return this.http.get<T>('/firstpage/', {params: queryParams}).pipe(
      catchError(() => of([] as unknown as T))
    );
  }
}
