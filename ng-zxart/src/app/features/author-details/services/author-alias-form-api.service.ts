import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {
  AuthorAliasCreateDto,
  AuthorAliasCreatedDto,
  AuthorAliasFormDto,
} from '../models/author-alias-form.dto';

@Injectable({providedIn: 'root'})
export class AuthorAliasFormApiService {
  private readonly apiUrl = '/author-alias-form/';

  constructor(private readonly http: HttpClient) {}

  getForm(authorId: number): Observable<AuthorAliasFormDto> {
    return this.http.get<AuthorAliasFormDto>(this.apiUrl, {
      params: {authorId},
    }).pipe(
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  create(request: AuthorAliasCreateDto): Observable<AuthorAliasCreatedDto> {
    return this.http.post<AuthorAliasCreatedDto>(this.apiUrl, request);
  }
}
