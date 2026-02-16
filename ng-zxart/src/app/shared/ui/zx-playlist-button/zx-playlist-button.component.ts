import {Component, HostListener, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {MatIconModule} from '@angular/material/icon';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {PlaylistService} from '../../services/playlist.service';
import {CurrentUserService} from '../../services/current-user.service';
import {PlaylistDto} from '../../models/playlist.model';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';

@Component({
  selector: 'zx-playlist-button',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    MatIconModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxButtonComponent,
    ZxInputComponent,
    ZxCaptionDirective,
  ],
  templateUrl: './zx-playlist-button.component.html',
  styleUrls: ['./zx-playlist-button.component.scss'],
})
export class ZxPlaylistButtonComponent {
  @Input() elementId!: number;

  popoverOpen = false;
  loading = false;
  activePlaylistIds: number[] = [];
  newPlaylistTitle = '';

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(
    private playlistService: PlaylistService,
    private currentUserService: CurrentUserService,
  ) {}

  get isAuthenticated(): boolean {
    return this.currentUserService.isAuthenticated;
  }

  get playlists(): PlaylistDto[] {
    return this.playlistService.getPlaylists();
  }

  togglePopover(event: Event): void {
    event.stopPropagation();
    if (!this.isAuthenticated) {
      return;
    }
    this.popoverOpen = !this.popoverOpen;
    if (this.popoverOpen) {
      this.loadPlaylistIds();
    }
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  onBackdropClick(): void {
    this.closePopover();
  }

  isInPlaylist(playlistId: number): boolean {
    return this.activePlaylistIds.includes(playlistId);
  }

  togglePlaylist(playlistId: number): void {
    if (this.isInPlaylist(playlistId)) {
      this.playlistService.removeFromPlaylist(playlistId, this.elementId).subscribe(ids => {
        this.activePlaylistIds = ids;
      });
    } else {
      this.playlistService.addToPlaylist(playlistId, this.elementId).subscribe(ids => {
        this.activePlaylistIds = ids;
      });
    }
  }

  createPlaylist(): void {
    const title = this.newPlaylistTitle.trim();
    if (!title) {
      return;
    }
    this.playlistService.createPlaylist(title).subscribe(() => {
      this.newPlaylistTitle = '';
    });
  }

  @HostListener('document:keydown.escape')
  onEscape(): void {
    if (this.popoverOpen) {
      this.closePopover();
    }
  }

  private loadPlaylistIds(): void {
    this.loading = true;
    this.playlistService.fetchPlaylistIds(this.elementId).subscribe(ids => {
      this.activePlaylistIds = ids;
      this.loading = false;
    });
  }

  trackById(_: number, item: PlaylistDto): number {
    return item.id;
  }
}
