import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, throwError} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {CommentDto, CommentsListDto} from '../models/comment.dto';

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

  getLatestComments(limit: number = 10): Observable<CommentDto[]> {
    return this.http.get<ApiResponse<CommentDto[]>>(`/comments/?action=latest&limit=${limit}`).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        return [];
      }),
      catchError(err => throwError(() => err))
    );
  }

  getComments(elementId: number): Observable<CommentDto[]> {
    return this.http.get<ApiResponse<CommentDto[]>>(`/comments/id:${elementId}/`).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        return [];
      }),
      catchError(err => throwError(() => err))
    );
  }

  getAllComments(page: number = 1): Observable<CommentsListDto> {
    return this.http.get<ApiResponse<CommentsListDto>>(`/comments/?action=list&page=${page}`).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        return {comments: [], currentPage: 1, pagesAmount: 0, totalCount: 0};
      }),
      catchError(err => throwError(() => err))
    );
  }

  addComment(targetId: number, content: string): Observable<CommentDto> {
    const body = new HttpParams()
      .set('id', targetId.toString())
      .set('content', content)
      .set('action', 'add');

    return this.http.post<ApiResponse<CommentDto>>('/comments/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        throw new Error(response.errorMessage || 'Failed to add comment');
      })
    );
  }

  updateComment(commentId: number, content: string): Observable<CommentDto> {
    const body = new HttpParams()
      .set('id', commentId.toString())
      .set('content', content)
      .set('action', 'update');

    return this.http.post<ApiResponse<CommentDto>>('/comments/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        throw new Error(response.errorMessage || 'Failed to update comment');
      })
    );
  }

  deleteComment(commentId: number): Observable<void> {
    const body = new HttpParams()
      .set('id', commentId.toString())
      .set('action', 'delete');

    return this.http.post<ApiResponse<void>>('/comments/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      map(response => {
        if (response.responseStatus === 'success') {
          return;
        }
        throw new Error(response.errorMessage || 'Failed to delete comment');
      })
    );
  }
}
