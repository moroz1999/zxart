import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {ManageUserDetailsDto, ManageUserSaveRequest, ManageUsersListDto, ManageUsersStatsDto} from '../models/manage-users.dto';
import {UsersListQuery, usersListRequestParams} from '../models/users-list-query';

/**
 * The users behind `/users-data/` and the dashboard figures behind
 * `/users-stats-data/`.
 *
 * Every page of the list is its own request: the list is searched, filtered
 * and sorted on the server, so nothing is cached here. Errors are not swallowed:
 * the screens show why a save or a ban was refused.
 */
@Injectable({
  providedIn: 'root',
})
export class ManageUsersApiService {
  private readonly apiUrl = '/users-data/';
  private readonly statsUrl = '/users-stats-data/';

  constructor(private readonly http: HttpClient) {}

  getList(query: UsersListQuery): Observable<ManageUsersListDto> {
    return this.http.get<ManageUsersListDto>(this.apiUrl, {params: usersListRequestParams(query)});
  }

  getDetails(id: number): Observable<ManageUserDetailsDto> {
    return this.http.get<ManageUserDetailsDto>(this.apiUrl, {params: {id}});
  }

  update(id: number, request: ManageUserSaveRequest): Observable<ManageUserDetailsDto> {
    return this.http.post<ManageUserDetailsDto>(this.apiUrl, request, {params: {id, action: 'update'}});
  }

  ban(id: number): Observable<ManageUserDetailsDto> {
    return this.http.post<ManageUserDetailsDto>(this.apiUrl, {}, {params: {id, action: 'ban'}});
  }

  getStats(): Observable<ManageUsersStatsDto> {
    return this.http.get<ManageUsersStatsDto>(this.statsUrl);
  }
}
