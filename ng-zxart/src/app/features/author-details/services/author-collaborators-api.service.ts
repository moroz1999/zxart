import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';

export interface CollaboratorPersonDto {
  id: number;
  title: string;
  url: string;
  jointPictures: number;
  jointTunes: number;
  jointProds: number;
  jointTotal: number;
}

export interface CollaboratorGroupDto {
  id: number;
  title: string;
  url: string;
  years: string | null;
  membersCount: number;
  jointProds: number;
}

export interface AuthorCollaboratorsDto {
  people: CollaboratorPersonDto[];
  groups: CollaboratorGroupDto[];
}

@Injectable({providedIn: 'root'})
export class AuthorCollaboratorsApiService {
  constructor(private readonly http: HttpClient) {}

  getCollaborators(authorId: number): Observable<AuthorCollaboratorsDto> {
    const params = new HttpParams().set('id', String(authorId));
    return this.http.get<AuthorCollaboratorsDto>('/author-collaborators/', {params}).pipe(
      catchError(() => of({people: [], groups: []})),
    );
  }
}
