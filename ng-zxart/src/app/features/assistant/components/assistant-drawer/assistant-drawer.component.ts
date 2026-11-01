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
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {BehaviorSubject, Subscription} from 'rxjs';
import {distinctUntilChanged, filter, map, switchMap} from 'rxjs/operators';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxCloseButtonComponent} from '../../../../shared/ui/zx-close-button/zx-close-button.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxInsetComponent} from '../../../../shared/ui/zx-inset/zx-inset.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxTextareaComponent} from '../../../../shared/ui/zx-textarea/zx-textarea.component';
import {ConfirmDialogService} from '../../../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {AssistantPlanView} from '../../models/assistant-view';
import {AssistantPanelService} from '../../services/assistant-panel.service';
import {AssistantStore} from '../../services/assistant-store.service';
import {AssistantMessageComponent} from '../assistant-message/assistant-message.component';
import {AssistantConversationsComponent} from '../assistant-conversations/assistant-conversations.component';
import {AssistantPageContextService} from '../../services/assistant-page-context.service';
import {AssistantConversationSummary} from '../../../../shared/models/assistant-data';
import {environment} from '../../../../../environments/environment';

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
    SvgIconComponent,
    ZxButtonComponent,
    ZxCloseButtonComponent,
    ZxInlineComponent,
    ZxInsetComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    ZxTextareaComponent,
    TextDirective,
    AssistantMessageComponent,
    AssistantConversationsComponent,
  ],
  templateUrl: './assistant-drawer.component.html',
  styleUrls: ['./assistant-drawer.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssistantDrawerComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('stream', {read: ElementRef}) private stream?: ElementRef<HTMLElement>;

  readonly view$ = this.store.view$;
  readonly conversations$ = this.store.conversations$;
  readonly page$ = this.pageContext.page$;
  readonly listOpen = new BehaviorSubject<boolean>(false);
  readonly text = new FormControl('', {nonNullable: true});

  private readonly subscription = new Subscription();

  constructor(
    private readonly store: AssistantStore,
    private readonly panel: AssistantPanelService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
    private readonly pageContext: AssistantPageContextService,
    iconReg: SvgIconRegistryService,
  ) {
    for (const icon of ['expand-more', 'expand-less']) {
      iconReg.loadSvg(`${environment.svgUrl}${icon}.svg`, icon)?.subscribe();
    }
  }

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
    this.listOpen.next(false);
  }

  toggleList(): void {
    const open = !this.listOpen.getValue();
    this.listOpen.next(open);
    if (open) {
      this.subscription.add(this.store.loadConversations().subscribe());
    }
  }

  pick(conversationId: number): void {
    this.store.select(conversationId);
    this.listOpen.next(false);
  }

  deleteConversation(conversation: AssistantConversationSummary): void {
    this.subscription.add(this.confirmDialog.confirm({
      title: this.translate.instant('assistant.delete-confirm.title'),
      message: this.translate.instant('assistant.delete-confirm.message', {title: conversation.title}),
      confirmLabel: this.translate.instant('assistant.delete'),
      cancelLabel: this.translate.instant('assistant.cancel'),
      danger: true,
    }).pipe(
      filter(confirmed => confirmed),
      switchMap(() => this.store.delete(conversation.id)),
    ).subscribe());
  }

  stop(): void {
    this.subscription.add(this.store.stop().subscribe());
  }

  close(): void {
    this.panel.close();
  }
}
