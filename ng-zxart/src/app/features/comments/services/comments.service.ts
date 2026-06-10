import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of, throwError} from 'rxjs';
import {catchError, map, switchMap, take} from 'rxjs/operators';
import {CommentDto, CommentsListDto} from '../models/comment.dto';
import {CurrentLanguageService} from '../../header/services/current-language.service';

@Injectable({
  providedIn: 'root'
})
export class CommentsService {
  constructor(
    private http: HttpClient,
    private currentLanguageService: CurrentLanguageService,
  ) {}

  getLatestComments(limit: number = 10): Observable<CommentDto[]> {
    return this.currentLanguageService.languageCode$.pipe(
      take(1),
      switchMap(languageCode => this.http.get<CommentDto[]>(`/comments/?action=latest&limit=${limit}&lang=${languageCode}`)),
      catchError(err => throwError(() => err))
    );
  }

  getComments(elementId: number): Observable<CommentDto[]> {
    return this.currentLanguageService.languageCode$.pipe(
      take(1),
      switchMap(languageCode => this.http.get<CommentDto[]>(`/comments/id:${elementId}/?lang=${languageCode}`)),
      map(comments => this.normalizeComments(comments)),
      catchError(err => throwError(() => err))
    );
  }

  getAllComments(page: number = 1): Observable<CommentsListDto> {
    return this.currentLanguageService.languageCode$.pipe(
      take(1),
      switchMap(languageCode => this.http.get<CommentsListDto>(`/comments/?action=list&page=${page}&lang=${languageCode}`)),
      catchError(err => throwError(() => err))
    );
  }

  getAuthorComments(authorId: number, page = 1, perPage = 50): Observable<CommentsListDto> {
    return this.currentLanguageService.languageCode$.pipe(
      take(1),
      switchMap(languageCode => this.http.get<CommentsListDto>(
        `/comments/?action=byAuthor&id=${authorId}&page=${page}&perPage=${perPage}&lang=${languageCode}`
      )),
      catchError(() => of({comments: [], currentPage: 1, pagesAmount: 0, totalCount: 0})),
    );
  }

  getGroupComments(groupId: number, page = 1, perPage = 50): Observable<CommentsListDto> {
    return this.currentLanguageService.languageCode$.pipe(
      take(1),
      switchMap(languageCode => this.http.get<CommentsListDto>(
        `/group-comments/?id=${groupId}&page=${page}&perPage=${perPage}&lang=${languageCode}`
      )),
      catchError(() => of({comments: [], currentPage: 1, pagesAmount: 0, totalCount: 0})),
    );
  }

  getPartyComments(partyId: number, page = 1, perPage = 50): Observable<CommentsListDto> {
    return this.currentLanguageService.languageCode$.pipe(
      take(1),
      switchMap(languageCode => this.http.get<CommentsListDto>(
        `/party-comments/?id=${partyId}&page=${page}&perPage=${perPage}&lang=${languageCode}`
      )),
      catchError(() => of({comments: [], currentPage: 1, pagesAmount: 0, totalCount: 0})),
    );
  }

  addComment(targetId: number, content: string): Observable<CommentDto> {
    const body = new HttpParams()
      .set('id', targetId.toString())
      .set('content', content)
      .set('action', 'add');

    return this.http.post<CommentDto>('/comments/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      catchError(err => throwError(() => new Error(err.error?.errorMessage || 'Failed to add comment')))
    );
  }

  updateComment(commentId: number, content: string): Observable<CommentDto> {
    const body = new HttpParams()
      .set('id', commentId.toString())
      .set('content', content)
      .set('action', 'update');

    return this.http.post<CommentDto>('/comments/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      catchError(err => throwError(() => new Error(err.error?.errorMessage || 'Failed to update comment')))
    );
  }

  deleteComment(commentId: number): Observable<void> {
    const body = new HttpParams()
      .set('id', commentId.toString())
      .set('action', 'delete');

    return this.http.post<null>('/comments/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      map(() => void 0),
      catchError(err => throwError(() => new Error(err.error?.errorMessage || 'Failed to delete comment')))
    );
  }

  private normalizeComments(comments: CommentDto[]): CommentDto[] {
    if (!comments.length) {
      return [];
    }

    const hasChildren = comments.some(comment => Array.isArray(comment.children) && comment.children.length > 0);
    const hasParent = comments.some(comment => comment.parentId);

    if (hasChildren || !hasParent) {
      return comments.map(comment => ({
        ...comment,
        children: Array.isArray(comment.children) ? comment.children : [],
      }));
    }

    const items = comments.map(comment => ({
      ...comment,
      children: Array.isArray(comment.children) ? comment.children : [],
    }));

    const index = new Map<number, CommentDto>();
    for (const item of items) {
      index.set(item.id, item);
    }

    const roots: CommentDto[] = [];
    for (const item of items) {
      const parentId = item.parentId;
      if (parentId && index.has(parentId)) {
        index.get(parentId)!.children.push(item);
      } else {
        roots.push(item);
      }
    }

    return roots;
  }
}
