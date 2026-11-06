import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {ActivatedRoute, Params, Router, RouterLink} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Observable, of, Subject, Subscription, switchMap} from 'rxjs';
import {catchError, debounceTime, distinctUntilChanged, map, scan, startWith} from 'rxjs/operators';
import {ZxManageTabsComponent} from '../../features/manage/components/zx-manage-tabs/zx-manage-tabs.component';
import {ManageUserGroupDto, ManageUserListItemDto, ManageUsersListDto} from '../../features/manage-users/models/manage-users.dto';
import {
  sortUsersListBy,
  UsersListQuery,
  usersListQueryFromParams,
  usersListQueryToParams,
  UsersListSort,
} from '../../features/manage-users/models/users-list-query';
import {ManageUserGroupsApiService} from '../../features/manage-users/services/manage-user-groups-api.service';
import {ManageUsersApiService} from '../../features/manage-users/services/manage-users-api.service';
import {PRIVILEGE_USER_BAN, RootPrivilegeService} from '../../shared/services/root-privilege.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxChipComponent} from '../../shared/ui/zx-chip/zx-chip.component';
import {ConfirmDialogService} from '../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {ZxFilterBarComponent} from '../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxFilterPickerComponent, ZxFilterPickerItem} from '../../shared/ui/zx-filter-picker/zx-filter-picker.component';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxLoadingStateDirective} from '../../shared/ui/zx-loading-state/zx-loading-state.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxPaginationComponent} from '../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';

interface UsersListState {
  list: ManageUsersListDto | null;
  loading: boolean;
  error: boolean;
}

interface UserRowVm {
  user: ManageUserListItemDto;
  groupNames: string[];
}

interface UsersPageVm {
  query: UsersListQuery;
  rows: UserRowVm[];
  total: number;
  pagesAmount: number;
  loading: boolean;
  error: boolean;
  groupItems: ZxFilterPickerItem[];
  selectedGroupIds: string[];
  statusItems: ZxFilterPickerItem[];
  selectedStatusIds: string[];
  /** The list's own params without the page, for the pagination links. */
  paginationParams: Params;
}

const STATUS_KEYS = ['manage-users.status-all', 'manage-users.status-active', 'manage-users.status-banned'];

/**
 * The users of the site (`/manage/users`), newest first.
 *
 * Every filter lives in the URL, so a filtered list can be shared and the
 * back button walks through the filters. The name/email search follows the
 * typing after a short pause; the group and status filters and the column
 * sorting apply at once and go back to the first page.
 */
@Component({
  selector: 'zx-manage-users-page',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterLink,
    TranslateModule,
    HeadingDirective,
    TextDirective,
    ZxButtonComponent,
    ZxChipComponent,
    ZxFilterBarComponent,
    ZxFilterPickerComponent,
    ZxFormMessageComponent,
    ZxInputComponent,
    ZxLoadingStateDirective,
    ZxManageTabsComponent,
    ZxPageLayoutComponent,
    ZxPaginationComponent,
    ZxStackComponent,
    ZxTableComponent,
  ],
  templateUrl: './manage-users-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageUsersPageComponent implements OnInit, OnDestroy {
  readonly mayBan$ = this.rootPrivilegeService.has(PRIVILEGE_USER_BAN);

  private readonly query$: Observable<UsersListQuery> = this.route.queryParamMap.pipe(
    map(params => usersListQueryFromParams(params)),
    distinctUntilChanged((previous, current) => JSON.stringify(previous) === JSON.stringify(current)),
  );

  /** Bumped after a ban, so the page is asked for again with the same query. */
  private readonly reload$ = new BehaviorSubject<void>(undefined);

  private readonly state$: Observable<UsersListState> = combineLatest([this.query$, this.reload$]).pipe(
    switchMap(([query]) => this.api.getList(query).pipe(
      map((list): Partial<UsersListState> => ({list, loading: false, error: false})),
      catchError(() => of<Partial<UsersListState>>({loading: false, error: true})),
      startWith<Partial<UsersListState>>({loading: true, error: false}),
    )),
    // the previous page stays on screen, dimmed, while the next one loads
    scan<Partial<UsersListState>, UsersListState>(
      (state, change) => ({...state, ...change}),
      {list: null, loading: true, error: false},
    ),
  );

  readonly vm$: Observable<UsersPageVm> = combineLatest([
    this.query$,
    this.state$,
    this.groupsApi.groups$,
    this.translate.stream(STATUS_KEYS),
  ]).pipe(
    map(([query, state, groups, statusTexts]) => this.buildVm(query, state, groups, statusTexts)),
  );

  errorMessage = '';

  private readonly search$ = new Subject<string>();
  private readonly subscriptions = new Subscription();

  constructor(
    private readonly route: ActivatedRoute,
    private readonly router: Router,
    private readonly api: ManageUsersApiService,
    private readonly groupsApi: ManageUserGroupsApiService,
    private readonly rootPrivilegeService: RootPrivilegeService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(
      this.search$.pipe(
        debounceTime(300),
        distinctUntilChanged(),
      ).subscribe(search => this.navigate({search: search.trim() === '' ? null : search, page: null})),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onSearchInput(value: string): void {
    this.search$.next(value);
  }

  onGroupChange(ids: string[]): void {
    this.navigate({group: ids[0] ?? null, page: null});
  }

  onStatusChange(ids: string[]): void {
    const status = ids[0];
    this.navigate({status: status === undefined || status === 'all' ? null : status, page: null});
  }

  onSort(query: UsersListQuery, sort: UsersListSort): void {
    this.router.navigate([], {relativeTo: this.route, queryParams: usersListQueryToParams(sortUsersListBy(query, sort))});
  }

  onPageChange(page: number): void {
    this.navigate({page: page > 1 ? page : null});
  }

  sortMark(query: UsersListQuery, sort: UsersListSort): string {
    if (query.sort !== sort) {
      return '';
    }
    return query.direction === 'asc' ? '▲' : '▼';
  }

  trackByUser(_index: number, row: UserRowVm): number {
    return row.user.id;
  }

  onBan(user: ManageUserListItemDto): void {
    const keys = ['manage-users.ban-confirm-title', 'manage-users.ban-confirm-message', 'manage-users.ban', 'form.cancel'];
    this.subscriptions.add(
      this.translate.get(keys, {name: user.userName}).pipe(
        switchMap((texts: Record<string, string>) => this.confirmDialog.confirm({
          title: texts['manage-users.ban-confirm-title'],
          message: texts['manage-users.ban-confirm-message'],
          confirmLabel: texts['manage-users.ban'],
          cancelLabel: texts['form.cancel'],
          danger: true,
        })),
      ).subscribe(confirmed => {
        if (confirmed) {
          this.runBan(user.id);
        }
      }),
    );
  }

  private runBan(id: number): void {
    this.errorMessage = '';
    this.subscriptions.add(
      this.api.ban(id).subscribe({
        next: () => this.reload$.next(),
        error: (error: HttpErrorResponse) => {
          this.errorMessage = error.error?.errorMessage ?? 'manage-users.error-ban';
          this.cdr.markForCheck();
        },
      }),
    );
  }

  private navigate(queryParams: Params): void {
    this.router.navigate([], {relativeTo: this.route, queryParams, queryParamsHandling: 'merge'});
  }

  private buildVm(
    query: UsersListQuery,
    state: UsersListState,
    groups: ManageUserGroupDto[],
    statusTexts: Record<string, string>,
  ): UsersPageVm {
    const groupNames = new Map(groups.map(group => [group.id, group.groupName]));
    const list = state.list;
    const {page: _page, ...paginationParams} = usersListQueryToParams(query);

    return {
      query,
      rows: (list?.users ?? []).map(user => ({
        user,
        groupNames: user.groupIds
          .map(groupId => groupNames.get(groupId))
          .filter((name): name is string => name !== undefined),
      })),
      total: list?.total ?? 0,
      pagesAmount: list ? Math.ceil(list.total / list.pageSize) : 0,
      loading: state.loading,
      error: state.error,
      groupItems: groups.map(group => ({id: String(group.id), label: group.groupName})),
      selectedGroupIds: query.groupId > 0 ? [String(query.groupId)] : [],
      statusItems: [
        {id: 'all', label: statusTexts['manage-users.status-all']},
        {id: 'active', label: statusTexts['manage-users.status-active']},
        {id: 'banned', label: statusTexts['manage-users.status-banned']},
      ],
      selectedStatusIds: [query.status],
      paginationParams,
    };
  }
}
