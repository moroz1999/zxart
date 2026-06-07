import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';

export interface GroupCollaboratorPersonDto {
  id: number;
  title: string;
  url: string;
  roles: string[];
  jointTotal: number;
}

export interface GroupCollaboratorGroupDto {
  id: number;
  title: string;
  url: string;
  years: string | null;
  membersCount: number;
  jointProds: number;
}

export interface GroupCollaboratorsDto {
  people: GroupCollaboratorPersonDto[];
  publishedGroups: GroupCollaboratorGroupDto[];
}

@Injectable({providedIn: 'root'})
export class GroupCollaboratorsApiService {
  constructor(private readonly http: HttpClient) {}

  getCollaborators(groupId: number): Observable<GroupCollaboratorsDto> {
    const params = new HttpParams().set('id', String(groupId));
    return this.http.get<GroupCollaboratorsDto>('/group-collaborators/', {params}).pipe(
      catchError(() => of({people: [], publishedGroups: []})),
    );
  }
}
