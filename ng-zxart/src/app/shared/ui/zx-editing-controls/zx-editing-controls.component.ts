import {AsyncPipe, DOCUMENT, NgForOf, NgIf} from '@angular/common';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {ChangeDetectionStrategy, Component, HostListener, inject, Input, OnChanges} from '@angular/core';
import {Router} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, firstValueFrom, Observable, of} from 'rxjs';
import {map, startWith, switchMap} from 'rxjs/operators';
import {isSpaUrl} from '../../utils/spa-url';
import {CurrentUserService} from '../../services/current-user.service';
import {ElementPrivilegesApiService} from '../../services/element-privileges-api.service';
import {FormSaveApiService} from '../../services/form-save-api.service';
import {ConfirmDialogService} from '../zx-confirm-dialog/confirm-dialog.service';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../zx-button-controls/zx-button-controls.component';
import {ZxSkeletonBoneComponent} from '../zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxEditButtonComponent} from '../zx-edit-button/zx-edit-button.component';
import {ZxStackComponent} from '../zx-stack/zx-stack.component';
import {PopoverAnimation} from '../../animations/popover-animations';

export interface ZxEditingControlConfirm {
  /** Dialog body text. */
  readonly messageKey: string;
  /** Dialog title; the action label is used when omitted. */
  readonly titleKey?: string;
  /** Confirm button label; the action label is used when omitted. */
  readonly confirmLabelKey?: string;
  /** Cancel button label; `form.cancel` is used when omitted. */
  readonly cancelLabelKey?: string;
  /** Renders the confirm button in the danger color. */
  readonly danger?: boolean;
}

/**
 * Runs a legacy element action through `/ajax/` instead of navigating to a
 * confirmation page. Combine with `confirm` so the dialog is the only step
 * between the button and the action.
 */
export interface ZxEditingControlRun {
  /** Legacy action name posted to `/ajax/`. */
  readonly action: string;
  /** Route prefix of the resulting entity — navigates to `/{targetPath}/{id}`. */
  readonly targetPath?: string;
  /** Result message shown when the action does not navigate. */
  readonly successKey?: string;
  /** Result message shown when the action fails. */
  readonly failureKey: string;
}

export interface ZxEditingControlAction {
  readonly action: string;
  readonly privilege: string;
  readonly labelKey: string;
  readonly color?: 'primary' | 'secondary' | 'danger' | 'transparent' | 'outlined';
  readonly confirm?: ZxEditingControlConfirm;
  readonly run?: ZxEditingControlRun;
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

interface EditingControlsVm {
  readonly loading: boolean;
  readonly actions: readonly VisibleEditingAction[];
}

@Component({
  selector: 'zx-editing-controls',
  standalone: true,
  imports: [
    AsyncPipe,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    NgForOf,
    NgIf,
    TranslateModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxEditButtonComponent,
    ZxSkeletonBoneComponent,
    ZxStackComponent,
  ],
  templateUrl: './zx-editing-controls.component.html',
  styleUrls: ['./zx-editing-controls.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [PopoverAnimation],
})
export class ZxEditingControlsComponent implements OnChanges {
  @Input({required: true}) elementId!: number;
  @Input({required: true}) actions: readonly ZxEditingControlAction[] = [];
  @Input({required: true}) buildActionUrl!: (action: string, elementId: number) => string;
  @Input() presentation: 'inline' | 'popover' = 'inline';
  @Input() popoverAriaLabel = '';
  @Input() size: 'xs' | 'sm' | 'md' | null = null;
  @Input() triggerIcon = 'edit';

  private readonly configStore = new BehaviorSubject<EditingControlsConfig | null>(null);
  readonly skeletonItems = [0, 1, 2];
  popoverOpen = false;

  readonly popoverPositions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
    {originX: 'start', originY: 'top', overlayX: 'start', overlayY: 'bottom', offsetY: -4},
  ];

  get inlineButtonSize(): 'xs' | 'sm' | 'md' {
    return this.size ?? 'md';
  }

  get popoverButtonSize(): 'xs' | 'sm' | 'md' {
    return this.size ?? 'sm';
  }

  readonly vm$: Observable<EditingControlsVm> = combineLatest([
    this.currentUserService.isAuthenticated$,
    this.configStore,
  ]).pipe(
    switchMap(([isAuthenticated, config]) => {
      if (!isAuthenticated || config === null || config.elementId <= 0 || config.actions.length === 0) {
        return of({loading: false, actions: []});
      }

      const privilegeNames = Array.from(new Set(config.actions.map(action => action.privilege)));
      return this.elementPrivilegesApi.getPrivileges(config.elementId, privilegeNames).pipe(
        map(privileges => config.actions
          .filter(action => privileges[action.privilege] === true)
          .map(action => ({
            action,
            url: config.buildActionUrl(action.action, config.elementId),
          }))),
        map(actions => ({loading: false, actions})),
        startWith({loading: true, actions: []}),
      );
    }),
  );

  private readonly document = inject(DOCUMENT);

  constructor(
    private readonly currentUserService: CurrentUserService,
    private readonly elementPrivilegesApi: ElementPrivilegesApiService,
    private readonly formSave: FormSaveApiService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
    private readonly router: Router,
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

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.popoverOpen = !this.popoverOpen;
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  async runAction(item: VisibleEditingAction): Promise<void> {
    if (item.action.confirm) {
      const confirmed = await this.confirm(item.action, item.action.confirm);
      if (!confirmed) {
        return;
      }
    }

    if (item.action.run) {
      await this.execute(item.action, item.action.run);
      return;
    }

    // Legacy CMS add-forms are still served as documents; SPA routes navigate
    // client-side.
    if (!isSpaUrl(item.url)) {
      this.document.location.href = item.url;
      return;
    }

    void this.router.navigateByUrl(item.url);
  }

  @HostListener('document:keydown.escape')
  onEscape(): void {
    if (this.popoverOpen) {
      this.closePopover();
    }
  }

  private async confirm(action: ZxEditingControlAction, confirm: ZxEditingControlConfirm): Promise<boolean> {
    const titleKey = confirm.titleKey ?? action.labelKey;
    const confirmLabelKey = confirm.confirmLabelKey ?? action.labelKey;
    const cancelLabelKey = confirm.cancelLabelKey ?? 'form.cancel';
    const data = await firstValueFrom(this.translate.get([
      titleKey,
      confirm.messageKey,
      confirmLabelKey,
      cancelLabelKey,
    ]));

    return firstValueFrom(this.confirmDialog.confirm({
      title: data[titleKey],
      message: data[confirm.messageKey],
      confirmLabel: data[confirmLabelKey],
      cancelLabel: data[cancelLabelKey],
      danger: confirm.danger === true,
    }));
  }

  private async execute(action: ZxEditingControlAction, run: ZxEditingControlRun): Promise<void> {
    try {
      const result = await firstValueFrom(this.formSave.save(this.elementId, {fields: {}}, run.action));
      if (run.targetPath) {
        void this.router.navigateByUrl(`/${run.targetPath}/${result.id}`);
        return;
      }
      const succeeded = (result as {success?: boolean}).success !== false;
      await this.notify(action, succeeded && run.successKey ? run.successKey : run.failureKey);
    } catch {
      await this.notify(action, run.failureKey);
    }
  }

  private async notify(action: ZxEditingControlAction, messageKey: string): Promise<void> {
    const data = await firstValueFrom(this.translate.get([action.labelKey, messageKey, 'form.ok']));

    await firstValueFrom(this.confirmDialog.notify({
      title: data[action.labelKey],
      message: data[messageKey],
      confirmLabel: data['form.ok'],
    }));
  }
}
