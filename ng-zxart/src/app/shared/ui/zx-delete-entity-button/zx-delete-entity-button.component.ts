import {AsyncPipe, NgIf} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, firstValueFrom, Observable, of} from 'rxjs';
import {map, switchMap} from 'rxjs/operators';
import {CurrentUserService} from '../../services/current-user.service';
import {ElementPrivilegesApiService} from '../../services/element-privileges-api.service';
import {EntityDeleteApiService} from '../../services/entity-delete-api.service';
import {ConfirmDialogService} from '../zx-confirm-dialog/confirm-dialog.service';
import {ZxEditButtonComponent} from '../zx-edit-button/zx-edit-button.component';

const DELETE_PRIVILEGE = 'publicDelete';

/**
 * Deletes the entity a form belongs to. Renders nothing unless the current user
 * holds `publicDelete` on the element; the deletion itself runs behind a
 * confirmation dialog and never navigates to a page of its own.
 */
@Component({
  selector: 'zx-delete-entity-button',
  standalone: true,
  imports: [
    AsyncPipe,
    NgIf,
    TranslateModule,
    ZxEditButtonComponent,
  ],
  templateUrl: './zx-delete-entity-button.component.html',
  styleUrl: './zx-delete-entity-button.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxDeleteEntityButtonComponent implements OnChanges {
  @Input({required: true}) elementId!: number;
  /** Route the user lands on once the entity is gone. */
  @Input({required: true}) redirectUrl!: string;
  /** Entity-specific action label used by the button and confirmation dialog. */
  @Input({required: true}) deleteLabelKey!: string;
  /** Dialog body; defaults to the generic entity wording. */
  @Input() confirmMessageKey = 'form.delete-confirm-message';

  private readonly elementStore = new BehaviorSubject<number>(0);

  readonly canDelete$: Observable<boolean> = combineLatest([
    this.currentUserService.isAuthenticated$,
    this.elementStore,
  ]).pipe(
    switchMap(([isAuthenticated, elementId]) => {
      if (!isAuthenticated || elementId <= 0) {
        return of(false);
      }
      return this.elementPrivilegesApi.getPrivileges(elementId, [DELETE_PRIVILEGE]).pipe(
        map(privileges => privileges[DELETE_PRIVILEGE] === true),
      );
    }),
  );

  constructor(
    private readonly currentUserService: CurrentUserService,
    private readonly elementPrivilegesApi: ElementPrivilegesApiService,
    private readonly entityDeleteApi: EntityDeleteApiService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
    private readonly router: Router,
  ) {}

  ngOnChanges(): void {
    this.elementStore.next(this.elementId);
  }

  async onDelete(): Promise<void> {
    const texts = await firstValueFrom(this.translate.get([
      this.deleteLabelKey,
      this.confirmMessageKey,
      'form.cancel',
      'form.delete-failed',
      'form.ok',
    ]));
    const deleteLabel = texts[this.deleteLabelKey];

    const confirmed = await firstValueFrom(this.confirmDialog.confirm({
      title: deleteLabel,
      message: texts[this.confirmMessageKey],
      confirmLabel: deleteLabel,
      cancelLabel: texts['form.cancel'],
      danger: true,
    }));
    if (!confirmed) {
      return;
    }

    const deleted = await firstValueFrom(this.entityDeleteApi.delete(this.elementId));
    if (!deleted) {
      await firstValueFrom(this.confirmDialog.notify({
        title: deleteLabel,
        message: texts['form.delete-failed'],
        confirmLabel: texts['form.ok'],
      }));
      return;
    }

    void this.router.navigateByUrl(this.redirectUrl);
  }
}
