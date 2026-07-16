import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxMusicListComponent} from '../../features/music-list/components/zx-music-list/zx-music-list.component';
import {ZxPicturesListComponent} from '../../features/picture-list/components/zx-pictures-list/zx-pictures-list.component';
import {ZxProdsListComponent} from '../../entities/zx-prods-list/zx-prods-list.component';

/** Routed page for `playlist/:id` — the playlist's pictures, music and software. */
@Component({
  selector: 'zx-playlist-page',
  standalone: true,
  imports: [CommonModule, ZxMusicListComponent, ZxPicturesListComponent, ZxProdsListComponent],
  templateUrl: './playlist-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PlaylistPageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
