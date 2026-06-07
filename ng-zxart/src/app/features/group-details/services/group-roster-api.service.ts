import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {GroupMemberDto, GroupSubgroupDto} from '../models/group-core.dto';

export interface GroupRosterDto {
  subgroups: GroupSubgroupDto[];
  members: GroupMemberDto[];
}

@Injectable({providedIn: 'root'})
export class GroupRosterApiService {
  constructor(private readonly http: HttpClient) {}

  getRoster(groupId: number): Observable<GroupRosterDto> {
    const params = new HttpParams().set('id', String(groupId));
    return this.http.get<GroupRosterDto>('/group-roster/', {params}).pipe(
      catchError(() => of({subgroups: [], members: []})),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }
}
