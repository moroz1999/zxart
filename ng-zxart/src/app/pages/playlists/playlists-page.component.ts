import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {PlaylistsApiService, UserPlaylist} from '../../features/playlists/services/playlists-api.service';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxInputComponent} from '../../shared/ui/zx-input/zx-input.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';

/** Routed page for `playlists` — manage the current user's playlists. */
@Component({
  selector: 'zx-playlists-page',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterLink, TranslateModule, ZxButtonComponent, ZxInputComponent, ZxSpinnerComponent],
  templateUrl: './playlists-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PlaylistsPageComponent implements OnInit, OnDestroy {
  playlists: UserPlaylist[] = [];
  loading = true;
  busy = false;
  newTitle = '';
  editingId: number | null = null;
  editingTitle = '';

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: PlaylistsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.subscriptions.add(this.api.list().subscribe(playlists => this.apply(playlists)));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  onCreate(): void {
    const title = this.newTitle.trim();
    if (!title || this.busy) {
      return;
    }
    this.busy = true;
    this.subscriptions.add(this.api.create(title).subscribe(playlists => {
      this.newTitle = '';
      this.apply(playlists);
    }));
  }

  startRename(playlist: UserPlaylist): void {
    this.editingId = playlist.id;
    this.editingTitle = playlist.title;
  }

  cancelRename(): void {
    this.editingId = null;
    this.editingTitle = '';
  }

  saveRename(id: number): void {
    const title = this.editingTitle.trim();
    if (!title || this.busy) {
      return;
    }
    this.busy = true;
    this.subscriptions.add(this.api.rename(id, title).subscribe(playlists => {
      this.cancelRename();
      this.apply(playlists);
    }));
  }

  onDelete(id: number): void {
    if (this.busy) {
      return;
    }
    this.busy = true;
    this.subscriptions.add(this.api.remove(id).subscribe(playlists => this.apply(playlists)));
  }

  trackById(_index: number, playlist: UserPlaylist): number {
    return playlist.id;
  }

  private apply(playlists: UserPlaylist[]): void {
    this.playlists = playlists;
    this.loading = false;
    this.busy = false;
    this.cdr.markForCheck();
  }
}
