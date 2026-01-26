import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, throwError} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {CommentDto} from '../models/comment.dto';

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
      }),
      catchError(err => throwError(() => err))
    );
  }

  addComment(targetId: number, content: string, author?: string): Observable<CommentDto> {
    const body = new HttpParams()
      .set('id', targetId.toString())
      .set('content', content)
      .set('author', author || '')
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
