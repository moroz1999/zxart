import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable, switchMap} from 'rxjs';
import {ZxMusicListComponent} from '../../features/music-list/components/zx-music-list/zx-music-list.component';
import {ZxPicturesListComponent} from '../../features/picture-list/components/zx-pictures-list/zx-pictures-list.component';
import {ZxProdsListComponent} from '../../entities/zx-prods-list/zx-prods-list.component';
import {ProdsBrowserService} from '../../features/prods-browser/services/prods-browser.service';
import {ZxProd} from '../../shared/models/zx-prod';
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

  /** Playlist software, linked to the playlist by the `playlist` link type. */
  readonly prods$: Observable<ZxProd[]> = this.elementId$.pipe(
    switchMap(elementId => this.prodsBrowserService.getAllByLink(elementId, 'playlist')),
    map(items => items.map(item => new ZxProd(item))),
  );

  constructor(
    private readonly route: ActivatedRoute,
    private readonly prodsBrowserService: ProdsBrowserService,
  ) {}
}
