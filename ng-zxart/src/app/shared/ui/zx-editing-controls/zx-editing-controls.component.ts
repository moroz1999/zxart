import {AsyncPipe, NgForOf, NgIf} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, firstValueFrom, Observable, of} from 'rxjs';
import {map, switchMap} from 'rxjs/operators';
import {CurrentUserService} from '../../services/current-user.service';
import {ElementPrivilegesApiService} from '../../services/element-privileges-api.service';
import {ConfirmDialogService} from '../zx-confirm-dialog/confirm-dialog.service';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../zx-button-controls/zx-button-controls.component';

export interface ZxEditingControlConfirm {
  readonly titleKey: string;
  readonly messageKey: string;
  readonly confirmLabelKey: string;
  readonly cancelLabelKey: string;
}

export interface ZxEditingControlAction {
  readonly action: string;
  readonly privilege: string;
  readonly labelKey: string;
  readonly color?: 'primary' | 'secondary' | 'danger' | 'transparent' | 'outlined';
  readonly confirm?: ZxEditingControlConfirm;
}

interface EditingControlsConfig {
  readonly elementId: number;
  readonly actions: readonly ZxEditingControlAction[];
  readonly buildActionUrl: (action: string, elementId: number) => string;
}

interface VisibleEditingAction {
  readonly action: ZxEditingControlAction;
  readonly url: string;
}

@Component({
  selector: 'zx-editing-controls',
  standalone: true,
  imports: [AsyncPipe, NgForOf, NgIf, TranslateModule, ZxButtonComponent, ZxButtonControlsComponent],
  templateUrl: './zx-editing-controls.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxEditingControlsComponent implements OnChanges {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) actions: readonly ZxEditingControlAction[] = [];
  @Input({required: true}) buildActionUrl!: (action: string, elementId: number) => string;

  private readonly configStore = new BehaviorSubject<EditingControlsConfig | null>(null);

  readonly visibleActions$: Observable<readonly VisibleEditingAction[]> = combineLatest([
    this.currentUserService.isAuthenticated$,
    this.configStore,
  ]).pipe(
    switchMap(([isAuthenticated, config]) => {
      if (!isAuthenticated || config === null || config.elementId <= 0 || config.actions.length === 0) {
        return of([]);
      }

      const privilegeNames = Array.from(new Set(config.actions.map(action => action.privilege)));
      return this.elementPrivilegesApi.getPrivileges(config.elementId, privilegeNames).pipe(
        map(privileges => config.actions
          .filter(action => privileges[action.privilege] === true)
          .map(action => ({
            action,
            url: config.buildActionUrl(action.action, config.elementId),
          }))),
      );
    }),
  );

  constructor(
    private readonly currentUserService: CurrentUserService,
    private readonly elementPrivilegesApi: ElementPrivilegesApiService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
  ) {}

  ngOnChanges(): void {
    if (!this.buildActionUrl) {
      this.configStore.next(null);
      return;
    }

    this.configStore.next({
      elementId: this.elementId,
      actions: this.actions,
      buildActionUrl: this.buildActionUrl,
    });
  }

  async runAction(item: VisibleEditingAction): Promise<void> {
    if (item.action.confirm) {
      const confirmed = await this.confirm(item.action.confirm);
      if (!confirmed) {
        return;
      }
    }

    window.location.href = item.url;
  }

  private async confirm(confirm: ZxEditingControlConfirm): Promise<boolean> {
    const data = await firstValueFrom(this.translate.get([
      confirm.titleKey,
      confirm.messageKey,
      confirm.confirmLabelKey,
      confirm.cancelLabelKey,
    ]));

    return firstValueFrom(this.confirmDialog.confirm({
      title: data[confirm.titleKey],
      message: data[confirm.messageKey],
      confirmLabel: data[confirm.confirmLabelKey],
      cancelLabel: data[confirm.cancelLabelKey],
      danger: true,
    }));
  }
}
