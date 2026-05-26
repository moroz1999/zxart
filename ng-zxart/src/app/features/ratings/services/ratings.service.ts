import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map, shareReplay} from 'rxjs/operators';
import {AuthorRatingsListDto, RecentRatingsListDto} from '../models/recent-rating.dto';
import {RatingDto} from '../models/rating.dto';

interface ElementRatingsListDto {
  items: RatingDto[];
}

@Injectable({
  providedIn: 'root'
})
export class RatingsService {
  constructor(private http: HttpClient) {}

  getRecentRatings(limit = 20, offset = 0): Observable<RecentRatingsListDto> {
    return this.http.get<RecentRatingsListDto>(`/ratings/?action=list&limit=${limit}&offset=${offset}`).pipe(
      catchError(() => of({items: [], hasMore: false})),
    );
  }

  getAuthorRatings(authorId: number, page = 1, perPage = 20): Observable<AuthorRatingsListDto> {
    return this.http.get<AuthorRatingsListDto>(
      `/ratings/?action=byAuthor&id=${authorId}&page=${page}&perPage=${perPage}`
    ).pipe(
      catchError(() => of({items: [], currentPage: 1, pagesAmount: 0, totalCount: 0})),
    );
  }

  getRatings(elementId: number): Observable<RatingDto[]> {
    return this.http.get<ElementRatingsListDto>(`/ratings/?id=${elementId}`).pipe(
      map(response => response.items ?? []),
      catchError(() => of([])),
      shareReplay({bufferSize: 1, refCount: false})
    );
  }
}
