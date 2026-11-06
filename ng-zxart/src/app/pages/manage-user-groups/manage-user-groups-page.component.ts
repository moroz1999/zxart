import {CommonModule} from '@angular/common';
import {HttpErrorResponse} from '@angular/common/http';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy} from '@angular/core';
import {RouterLink} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subscription, switchMap} from 'rxjs';
import {ZxManageTabsComponent} from '../../features/manage/components/zx-manage-tabs/zx-manage-tabs.component';
import {ManageUserGroupDto} from '../../features/manage-users/models/manage-users.dto';
import {ManageUserGroupsApiService} from '../../features/manage-users/services/manage-user-groups-api.service';
import {
  PRIVILEGE_USER_GROUP_DELETE,
  ROOT_PRIVILEGE_MANAGE_USERS,
  RootPrivilegeService,
} from '../../shared/services/root-privilege.service';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ConfirmDialogService} from '../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {ZxFormMessageComponent} from '../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';

/**
 * The user groups (`/manage/user-groups`). A group's member count opens the
 * users list filtered to it, for whoever may see that list. Deleting a group
 * keeps its users and only takes the group away from them.
 */
@Component({
  selector: 'zx-manage-user-groups-page',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    TranslateModule,
    HeadingDirective,
    TextDirective,
    ZxButtonComponent,
    ZxFormMessageComponent,
    ZxManageTabsComponent,
    ZxPageLayoutComponent,
    ZxStackComponent,
    ZxTableComponent,
  ],
  templateUrl: './manage-user-groups-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageUserGroupsPageComponent implements OnDestroy {
  readonly groups$ = this.api.groups$;
  readonly mayDelete$ = this.rootPrivilegeService.has(PRIVILEGE_USER_GROUP_DELETE);
  readonly mayListUsers$ = this.rootPrivilegeService.has(ROOT_PRIVILEGE_MANAGE_USERS);

  errorMessage = '';

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: ManageUserGroupsApiService,
    private readonly rootPrivilegeService: RootPrivilegeService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  trackByGroup(_index: number, group: ManageUserGroupDto): number {
    return group.id;
  }

  onDelete(group: ManageUserGroupDto): void {
    const keys = [
      'manage-user-groups.delete-confirm-title',
      'manage-user-groups.delete-confirm-message',
      'manage-user-groups.delete',
      'form.cancel',
    ];
    this.subscriptions.add(
      this.translate.get(keys, {name: group.groupName, count: group.membersCount}).pipe(
        switchMap((texts: Record<string, string>) => this.confirmDialog.confirm({
          title: texts['manage-user-groups.delete-confirm-title'],
          message: texts['manage-user-groups.delete-confirm-message'],
          confirmLabel: texts['manage-user-groups.delete'],
          cancelLabel: texts['form.cancel'],
          danger: true,
        })),
      ).subscribe(confirmed => {
        if (confirmed) {
          this.runDelete(group.id);
        }
      }),
    );
  }

  private runDelete(id: number): void {
    this.errorMessage = '';
    this.subscriptions.add(
      this.api.delete(id).subscribe({
        error: (error: HttpErrorResponse) => {
          this.errorMessage = error.error?.errorMessage ?? 'manage-user-groups.error-delete';
          this.cdr.markForCheck();
        },
      }),
    );
  }
}
