import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable} from 'rxjs';
import {ZxMusicListComponent} from '../../features/music-list/components/zx-music-list/zx-music-list.component';
import {ZxPicturesListComponent} from '../../features/picture-list/components/zx-pictures-list/zx-pictures-list.component';
import {ZxProdsListComponent} from '../../entities/zx-prods-list/zx-prods-list.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed page for `playlist/:id` — the playlist's pictures, music and software. */
@Component({
  selector: 'zx-playlist-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxMusicListComponent,
    ZxPicturesListComponent,
    ZxProdsListComponent,
    HeadingDirective,
    ZxPageLayoutComponent,
  ],
  templateUrl: './playlist-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PlaylistPageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
