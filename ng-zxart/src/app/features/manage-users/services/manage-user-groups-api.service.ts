import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {BehaviorSubject, defer, Observable} from 'rxjs';
import {filter, tap} from 'rxjs/operators';
import {ManageUserGroupDto, ManageUserGroupSaveRequest} from '../models/manage-users.dto';

/**
 * The user groups behind `/user-groups-data/`: a few dozen rows, read once
 * and replaced from every write's response. Errors of writes are not swallowed:
 * the screen has to show why a save or a delete was refused.
 */
@Injectable({
  providedIn: 'root',
})
export class ManageUserGroupsApiService {
  private readonly apiUrl = '/user-groups-data/';
  private readonly store = new BehaviorSubject<ManageUserGroupDto[] | null>(null);
  private loading = false;

  readonly groups$: Observable<ManageUserGroupDto[]> = defer(() => {
    if (this.store.getValue() === null && !this.loading) {
      this.load();
    }
    return this.store.pipe(filter((groups): groups is ManageUserGroupDto[] => groups !== null));
  });

  constructor(private readonly http: HttpClient) {}

  create(request: ManageUserGroupSaveRequest): Observable<ManageUserGroupDto[]> {
    return this.post({action: 'create'}, request);
  }

  update(id: number, request: ManageUserGroupSaveRequest): Observable<ManageUserGroupDto[]> {
    return this.post({id, action: 'update'}, request);
  }

  delete(id: number): Observable<ManageUserGroupDto[]> {
    return this.post({id, action: 'delete'}, {});
  }

  /** The member counts move when a user's groups are saved elsewhere. */
  refresh(): void {
    if (!this.loading) {
      this.load();
    }
  }

  private post(params: Record<string, string | number>, body: unknown): Observable<ManageUserGroupDto[]> {
    return this.http
      .post<ManageUserGroupDto[]>(this.apiUrl, body, {params})
      .pipe(tap(groups => this.store.next(groups)));
  }

  private load(): void {
    this.loading = true;
    this.http.get<ManageUserGroupDto[]>(this.apiUrl).subscribe({
      next: groups => {
        this.loading = false;
        this.store.next(groups);
      },
      error: () => {
        this.loading = false;
        this.store.next([]);
      },
    });
  }
}
