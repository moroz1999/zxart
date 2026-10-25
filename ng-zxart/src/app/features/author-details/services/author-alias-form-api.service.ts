import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {AuthorAliasFormDto} from '../models/author-alias-form.dto';

@Injectable({providedIn: 'root'})
export class AuthorAliasFormApiService {
  private readonly apiUrl = '/author-alias-form/';

  constructor(private readonly http: HttpClient) {}

  getForm(authorId: number): Observable<AuthorAliasFormDto> {
    return this.http.get<AuthorAliasFormDto>(this.apiUrl, {
      params: {authorId},
    }).pipe(
      catchError(error => of({
        author: {id: 0, title: ''},
        errorMessage: error?.error?.errorMessage ?? 'author-alias-form.error-load',
      })),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }
}
