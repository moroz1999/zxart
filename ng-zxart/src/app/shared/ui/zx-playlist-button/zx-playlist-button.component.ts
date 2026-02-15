import {AfterViewChecked, Component, ElementRef, HostListener, Input, ViewChild,} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {MatIconModule} from '@angular/material/icon';
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
    ZxButtonComponent,
    ZxInputComponent,
    ZxCaptionDirective,
  ],
  templateUrl: './zx-playlist-button.component.html',
  styleUrls: ['./zx-playlist-button.component.scss'],
})
export class ZxPlaylistButtonComponent implements AfterViewChecked {
  @Input() elementId!: number;

  @ViewChild('popoverEl') popoverEl?: ElementRef<HTMLElement>;

  popoverOpen = false;
  dropUp = false;
  loading = false;
  activePlaylistIds: number[] = [];
  newPlaylistTitle = '';

  constructor(
    private elementRef: ElementRef<HTMLElement>,
    private playlistService: PlaylistService,
    private currentUserService: CurrentUserService,
  ) {}

  ngAfterViewChecked(): void {
    if (this.popoverOpen && this.popoverEl) {
      this.updateDropDirection();
    }
  }

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

  @HostListener('document:click', ['$event'])
  onDocumentClick(event: Event): void {
    if (!this.popoverOpen) {
      return;
    }
    const target = event.target as Node | null;
    if (target && this.elementRef.nativeElement.contains(target)) {
      return;
    }
    this.closePopover();
  }

  @HostListener('document:keydown.escape', ['$event'])
  onEscape(event: KeyboardEvent): void {
    if (this.popoverOpen) {
      event.stopPropagation();
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

  private updateDropDirection(): void {
    const hostRect = this.elementRef.nativeElement.getBoundingClientRect();
    const popoverHeight = this.popoverEl!.nativeElement.offsetHeight;
    const spaceBelow = window.innerHeight - hostRect.bottom;
    const spaceAbove = hostRect.top;
    this.dropUp = spaceBelow < popoverHeight && spaceAbove > spaceBelow;
  }

  trackById(_: number, item: PlaylistDto): number {
    return item.id;
  }
}
