import {
  AfterViewInit,
  ChangeDetectionStrategy,
  Component,
  ElementRef,
  OnDestroy,
  OnInit,
  ViewChild,
} from '@angular/core';
import {AsyncPipe} from '@angular/common';
import {FormControl, ReactiveFormsModule} from '@angular/forms';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {distinctUntilChanged, filter, map, switchMap} from 'rxjs/operators';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxCloseButtonComponent} from '../../../../shared/ui/zx-close-button/zx-close-button.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxTextareaComponent} from '../../../../shared/ui/zx-textarea/zx-textarea.component';
import {ConfirmDialogService} from '../../../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {AssistantPlanView} from '../../models/assistant-view';
import {AssistantPanelService} from '../../services/assistant-panel.service';
import {AssistantStore} from '../../services/assistant-store.service';
import {AssistantMessageComponent} from '../assistant-message/assistant-message.component';

/**
 * The assistant's drawer, above the page and outside the router outlet, so it
 * stays open while the user follows the links it gives. It polls the open
 * conversation for as long as it exists.
 */
@Component({
  selector: 'zx-assistant-drawer',
  standalone: true,
  imports: [
    AsyncPipe,
    ReactiveFormsModule,
    TranslateModule,
    ZxButtonComponent,
    ZxCloseButtonComponent,
    ZxSpinnerComponent,
    ZxTextareaComponent,
    AssistantMessageComponent,
  ],
  templateUrl: './assistant-drawer.component.html',
  styleUrls: ['./assistant-drawer.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantDrawerComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('stream') private stream?: ElementRef<HTMLElement>;

  readonly view$ = this.store.view$;
  readonly text = new FormControl('', {nonNullable: true});

  private readonly subscription = new Subscription();

  constructor(
    private readonly store: AssistantStore,
    private readonly panel: AssistantPanelService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
  ) {}

  ngOnInit(): void {
    this.subscription.add(this.store.polling$.subscribe());
  }

  ngAfterViewInit(): void {
    // a new message scrolls the stream to the bottom
    this.subscription.add(this.store.view$.pipe(
      map(view => view.messages.length + (view.running ? 1 : 0)),
      distinctUntilChanged(),
    ).subscribe(() => requestAnimationFrame(() => {
      const element = this.stream?.nativeElement;
      if (element) {
        element.scrollTop = element.scrollHeight;
      }
    })));
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  submit(): void {
    const text = this.text.value.trim();
    if (text === '') {
      return;
    }
    this.text.setValue('');
    this.subscription.add(this.store.send(text).subscribe());
  }

  /** Enter sends; Shift+Enter is a new line. */
  onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
      event.preventDefault();
      this.submit();
    }
  }

  approve(plan: AssistantPlanView): void {
    this.subscription.add(this.confirmDialog.confirm({
      title: this.translate.instant('assistant.approve-confirm.title'),
      message: this.translate.instant(
        plan.destructiveCount > 0 ? 'assistant.approve-confirm.message-destructive' : 'assistant.approve-confirm.message',
        {count: plan.actions.length, destructive: plan.destructiveCount},
      ),
      confirmLabel: this.translate.instant('assistant.approve'),
      cancelLabel: this.translate.instant('assistant.cancel'),
      danger: plan.destructiveCount > 0,
    }).pipe(
      filter(confirmed => confirmed),
      switchMap(() => this.store.approvePlan(plan.id)),
    ).subscribe());
  }

  startNew(): void {
    this.store.startNew();
  }

  close(): void {
    this.panel.close();
  }
}
