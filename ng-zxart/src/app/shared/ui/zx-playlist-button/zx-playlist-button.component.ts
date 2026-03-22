import {ChangeDetectionStrategy, Component, HostListener, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {BehaviorSubject, Observable} from 'rxjs';
import {take} from 'rxjs/operators';
import {PlaylistService} from '../../services/playlist.service';
import {CurrentUserService} from '../../services/current-user.service';
import {PlaylistDto} from '../../models/playlist.model';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxInputComponent} from '../zx-input/zx-input.component';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';
import {environment} from '../../../../environments/environment';

@Component({
  selector: 'zx-playlist-button',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    TranslateModule,
    SvgIconComponent,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxButtonComponent,
    ZxInputComponent,
    ZxCaptionDirective,
  ],
  templateUrl: './zx-playlist-button.component.html',
  styleUrls: ['./zx-playlist-button.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPlaylistButtonComponent implements OnInit {
  @Input() elementId!: number;

  popoverOpen = false;
  newPlaylistTitle = '';

  private readonly activePlaylistIdsSubject = new BehaviorSubject<number[]>([]);
  readonly activePlaylistIds$ = this.activePlaylistIdsSubject.asObservable();

  readonly isAuthenticated$: Observable<boolean> = this.currentUserService.isAuthenticated$;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
    {originX: 'start', originY: 'bottom', overlayX: 'start', overlayY: 'top', offsetY: 4},
    {originX: 'start', originY: 'top', overlayX: 'start', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(
    private playlistService: PlaylistService,
    private currentUserService: CurrentUserService,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}favorite-border.svg`, 'favorite-border')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}check.svg`, 'check')?.subscribe();
  }

  get playlists(): PlaylistDto[] {
    return this.playlistService.getPlaylists();
  }

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.currentUserService.isAuthenticated$.pipe(take(1)).subscribe(isAuthenticated => {
      if (!isAuthenticated) {
        return;
      }
      this.popoverOpen = !this.popoverOpen;
      if (this.popoverOpen) {
        this.loadPlaylistIds();
      }
    });
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  onBackdropClick(): void {
    this.closePopover();
  }

  isInPlaylist(activeIds: number[], playlistId: number): boolean {
    return activeIds.includes(playlistId);
  }

  togglePlaylist(playlistId: number, activeIds: number[]): void {
    const obs = activeIds.includes(playlistId)
      ? this.playlistService.removeFromPlaylist(playlistId, this.elementId)
      : this.playlistService.addToPlaylist(playlistId, this.elementId);
    obs.subscribe(ids => this.activePlaylistIdsSubject.next(ids));
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
    this.playlistService.fetchPlaylistIds(this.elementId).subscribe(ids => {
      this.activePlaylistIdsSubject.next(ids);
    });
  }

  trackById(_: number, item: PlaylistDto): number {
    return item.id;
  }
}
