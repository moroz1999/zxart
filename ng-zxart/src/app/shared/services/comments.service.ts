import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';

export interface CommentDto {
  id: number;
  author: string;
  authorUrl?: string;
  authorBadge?: string;
  date: string;
  content: string;
  votes: number;
  votingDenied: boolean;
  commentsAllowed: boolean;
  parentId?: number;
  children: CommentDto[];
}

interface ApiResponse<T> {
  responseStatus: string;
  responseData?: T;
  errorMessage?: string;
}

@Injectable({
  providedIn: 'root'
})
export class CommentsService {
  constructor(private http: HttpClient) {}

  getComments(elementId: number): Observable<CommentDto[]> {
    return this.http.get<ApiResponse<CommentDto[]>>(`/comments/id:${elementId}/`).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        return [];
      })
    );
  }
}
