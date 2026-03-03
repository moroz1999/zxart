import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';
import {PartyDto} from '../../../shared/models/party-dto';
import {ZxProd} from '../../../shared/models/zx-prod';

@Injectable({
  providedIn: 'root'
})
export class FirstpageDataService {
  constructor(private http: HttpClient) {}

  getNewProds(limit: number, minRating: number, startYearOffset: number): Observable<ZxProd[]> {
    return this.get<ZxProdDto[]>('newProds', {limit, minRating, startYearOffset}).pipe(
      map(dtos => dtos.map(dto => new ZxProd(dto)))
    );
  }

  getNewPictures(limit: number): Observable<ZxPictureDto[]> {
    return this.get<ZxPictureDto[]>('newPictures', {limit});
  }

  getNewTunes(limit: number): Observable<ZxTuneDto[]> {
    return this.get<ZxTuneDto[]>('newTunes', {limit});
  }

  getBestNewDemos(limit: number, minRating: number): Observable<ZxProd[]> {
    return this.get<ZxProdDto[]>('bestNewDemos', {limit, minRating}).pipe(
      map(dtos => dtos.map(dto => new ZxProd(dto)))
    );
  }

  getBestNewGames(limit: number, minRating: number): Observable<ZxProd[]> {
    return this.get<ZxProdDto[]>('bestNewGames', {limit, minRating}).pipe(
      map(dtos => dtos.map(dto => new ZxProd(dto)))
    );
  }

  getRecentParties(limit: number): Observable<PartyDto[]> {
    return this.get<PartyDto[]>('recentParties', {limit});
  }

  getBestPicturesOfMonth(limit: number): Observable<ZxPictureDto[]> {
    return this.get<ZxPictureDto[]>('bestPicturesOfMonth', {limit});
  }

  getLatestAddedProds(limit: number): Observable<ZxProd[]> {
    return this.get<ZxProdDto[]>('latestAddedProds', {limit}).pipe(
      map(dtos => dtos.map(dto => new ZxProd(dto)))
    );
  }

  getLatestAddedReleases(limit: number): Observable<ZxProd[]> {
    return this.get<ZxProdDto[]>('latestAddedReleases', {limit}).pipe(
      map(dtos => dtos.map(dto => new ZxProd(dto)))
    );
  }

  getSupportProds(limit: number): Observable<ZxProd[]> {
    return this.get<ZxProdDto[]>('supportProds', {limit}).pipe(
      map(dtos => dtos.map(dto => new ZxProd(dto)))
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
