import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable} from 'rxjs';
import {ZxProdsCategoryComponent} from '../../entities/zx-prods-category/zx-prods-category.component';
import {ZxGroupBrowserComponent} from '../../features/group-browser/components/zx-group-browser/zx-group-browser.component';
import {ZxPictureBrowserComponent} from '../../features/picture-browser/components/zx-picture-browser/zx-picture-browser.component';
import {ZxMusicBrowserComponent} from '../../features/music-browser/components/zx-music-browser/zx-music-browser.component';
import {ZxAuthorBrowserComponent} from '../../features/author-browser/components/zx-author-browser/zx-author-browser.component';

type CollectionKind = 'prods' | 'groups' | 'pictures' | 'music' | 'authors';

interface CollectionVm {
  kind: CollectionKind;
  letter: string;
}

/**
 * Routed collection/list page (`/prods`, `/groups`, `/pictures`, `/music`,
 * `/authors`). Mounts the matching browser with no wrapper element id: each
 * browser's backend endpoint resolves the catalogue root by type. The `kind`
 * comes from route data.
 */
@Component({
  selector: 'zx-collection-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdsCategoryComponent,
    ZxGroupBrowserComponent,
    ZxPictureBrowserComponent,
    ZxMusicBrowserComponent,
    ZxAuthorBrowserComponent,
  ],
  templateUrl: './collection-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CollectionPageComponent {
  readonly vm$: Observable<CollectionVm> = this.route.paramMap.pipe(
    map(params => ({
      kind: this.kind,
      letter: params.get('letter') ?? '',
    })),
  );

  private get kind(): CollectionKind {
    return (this.route.snapshot.data['kind'] ?? 'prods') as CollectionKind;
  }

  constructor(private readonly route: ActivatedRoute) {}
}
