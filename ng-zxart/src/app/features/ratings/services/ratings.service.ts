import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {RecentRatingDto, RecentRatingsListDto} from '../models/recent-rating.dto';
import {RatingDto} from '../models/rating.dto';

interface ApiResponse<T> {
  responseStatus: string;
  responseData?: T;
  errorMessage?: string;
}

interface ElementRatingsListDto {
  items: RatingDto[];
}

@Injectable({
  providedIn: 'root'
})
export class RatingsService {
  constructor(private http: HttpClient) {}

  getRecentRatings(limit = 20): Observable<RecentRatingDto[]> {
    return this.http.get<ApiResponse<RecentRatingsListDto>>(`/ratings/?action=list&limit=${limit}`).pipe(
      map(response => response.responseStatus === 'success' && response.responseData
        ? response.responseData.items : []),
      catchError(() => of([]))
    );
  }

  getRatings(elementId: number): Observable<RatingDto[]> {
    return this.http.get<ApiResponse<ElementRatingsListDto>>(`/ratings/id:${elementId}/`).pipe(
      map(response => response.responseStatus === 'success' && response.responseData
        ? response.responseData.items : []),
      catchError(() => of([]))
    );
  }
}
