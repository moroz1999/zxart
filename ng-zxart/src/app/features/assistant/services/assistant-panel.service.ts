import {Injectable} from '@angular/core';
import {BehaviorSubject, combineLatest, Observable} from 'rxjs';
import {map, shareReplay} from 'rxjs/operators';
import {LocalStorageService} from '../../../shared/services/local-storage.service';
import {ROOT_PRIVILEGE_AI_ASSISTANT, RootPrivilegeService} from '../../../shared/services/root-privilege.service';

const OPEN_KEY = 'assistant-open';

/**
 * Whether the assistant's drawer is open. It exists only for a user holding the
 * `aiAssistant` privilege; the open state is remembered per browser.
 */
@Injectable({providedIn: 'root'})
export class AssistantPanelService {
  private readonly openStore: BehaviorSubject<boolean>;

  readonly available$: Observable<boolean>;
  readonly open$: Observable<boolean>;

  constructor(
    private readonly storage: LocalStorageService,
    rootPrivilegeService: RootPrivilegeService,
  ) {
    this.openStore = new BehaviorSubject<boolean>(this.storage.get<boolean>(OPEN_KEY) === true);
    this.available$ = rootPrivilegeService.has(ROOT_PRIVILEGE_AI_ASSISTANT).pipe(
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.open$ = combineLatest([this.available$, this.openStore]).pipe(map(([available, open]) => available && open));
  }

  toggle(): void {
    this.setOpen(!this.openStore.getValue());
  }

  close(): void {
    this.setOpen(false);
  }

  private setOpen(open: boolean): void {
    this.storage.set(OPEN_KEY, open);
    this.openStore.next(open);
  }
}
