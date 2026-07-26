import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, OnDestroy} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {RouterLink} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Observable, Subscription} from 'rxjs';
import {filter, map, switchMap} from 'rxjs/operators';
import {UserPlaylist} from '../../features/playlists/models/user-playlist';
import {PlaylistsApiService} from '../../features/playlists/services/playlists-api.service';
import {ConfirmDialogService} from '../../shared/ui/zx-confirm-dialog/confirm-dialog.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxCounterItem, ZxCountersComponent} from '../../shared/ui/zx-counters/zx-counters.component';
import {ZxFormActionsComponent} from '../../shared/ui/zx-form/zx-form-actions/zx-form-actions.component';
import {ZxFormControlComponent} from '../../shared/ui/zx-form/zx-form-control/zx-form-control.component';
import {ZxFormFieldComponent} from '../../shared/ui/zx-form/zx-form-field/zx-form-field.component';
import {ZxFormLabelComponent} from '../../shared/ui/zx-form/zx-form-label/zx-form-label.component';
import {ZxFormDirective} from '../../shared/ui/zx-form/zx-form.directive';
import {ZxInlineComponent} from '../../shared/ui/zx-inline/zx-inline.component';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** One playlist as rendered: the stored playlist plus its ready-to-render counters. */
interface PlaylistRow {
  readonly playlist: UserPlaylist;
  readonly counters: ZxCounterItem[];
}

/** Transient state of the page itself — what is being edited and what is in flight. */
interface PlaylistsUiState {
  readonly busy: boolean;
  readonly editingId: number | null;
}

interface PlaylistsVm extends PlaylistsUiState {
  readonly loading: boolean;
  readonly rows: PlaylistRow[];
}

/** Routed page for `playlists` — manage the current user's playlists. */
@Component({
  selector: 'zx-playlists-page',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterLink,
    TranslateModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxCountersComponent,
    ZxFormActionsComponent,
    ZxFormControlComponent,
    ZxFormFieldComponent,
    ZxFormLabelComponent,
    ZxFormDirective,
    ZxInlineComponent,
    ZxInputComponent,
    ZxPanelComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './playlists-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PlaylistsPageComponent implements OnDestroy {
  newTitle = '';
  editingTitle = '';

  private readonly uiState = new BehaviorSubject<PlaylistsUiState>({busy: false, editingId: null});

  readonly vm$: Observable<PlaylistsVm> = combineLatest([this.api.playlists$, this.uiState]).pipe(
    map(([playlists, ui]) => ({
      ...ui,
      loading: playlists === null,
      rows: (playlists ?? []).map(playlist => ({playlist, counters: this.buildCounters(playlist)})),
    })),
  );

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: PlaylistsApiService,
    private readonly confirmDialog: ConfirmDialogService,
    private readonly translate: TranslateService,
  ) {}

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCreate(): void {
    const title = this.newTitle.trim();
    if (title === '' || this.uiState.getValue().busy) {
      return;
    }
    this.newTitle = '';
    this.run(this.api.create(title));
  }

  startRename(playlist: UserPlaylist): void {
    this.editingTitle = playlist.title;
    this.uiState.next({...this.uiState.getValue(), editingId: playlist.id});
  }

  cancelRename(): void {
    this.editingTitle = '';
    this.uiState.next({...this.uiState.getValue(), editingId: null});
  }

  saveRename(id: number): void {
    const title = this.editingTitle.trim();
    if (title === '' || this.uiState.getValue().busy) {
      return;
    }
    this.cancelRename();
    this.run(this.api.rename(id, title));
  }

  onDelete(playlist: UserPlaylist): void {
    if (this.uiState.getValue().busy) {
      return;
    }
    this.run(this.confirmDelete(playlist).pipe(
      filter(confirmed => confirmed),
      switchMap(() => this.api.remove(playlist.id)),
    ));
  }

  trackById(_index: number, row: PlaylistRow): number {
    return row.playlist.id;
  }

  private confirmDelete(playlist: UserPlaylist): Observable<boolean> {
    const keys = [
      'playlists.delete-confirm-title',
      'playlists.delete-confirm-message',
      'playlists.delete',
      'form.cancel',
    ];

    return this.translate.get(keys, {title: playlist.title}).pipe(
      switchMap(texts => this.confirmDialog.confirm({
        title: texts['playlists.delete-confirm-title'],
        message: texts['playlists.delete-confirm-message'],
        confirmLabel: texts['playlists.delete'],
        cancelLabel: texts['form.cancel'],
        danger: true,
      })),
    );
  }

  /** Runs one mutation, keeping the page blocked while it is in flight. */
  private run(mutation: Observable<unknown>): void {
    this.setBusy(true);
    this.subscriptions.add(mutation.subscribe({
      complete: () => this.setBusy(false),
      error: () => this.setBusy(false),
    }));
  }

  private setBusy(busy: boolean): void {
    this.uiState.next({...this.uiState.getValue(), busy});
  }

  private buildCounters(playlist: UserPlaylist): ZxCounterItem[] {
    const counters: ZxCounterItem[] = [];
    if (playlist.pictures > 0) {
      counters.push({value: playlist.pictures, labelKey: 'playlists.counter.pictures'});
    }
    if (playlist.tunes > 0) {
      counters.push({value: playlist.tunes, labelKey: 'playlists.counter.tunes'});
    }
    if (playlist.prods > 0) {
      counters.push({value: playlist.prods, labelKey: 'playlists.counter.prods'});
    }
    return counters;
  }
}
