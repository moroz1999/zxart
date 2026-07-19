import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable} from 'rxjs';
import {ZxProdsCategoryComponent} from '../../entities/zx-prods-category/zx-prods-category.component';
import {ZxGroupBrowserComponent} from '../../features/group-browser/components/zx-group-browser/zx-group-browser.component';
import {ZxAuthorBrowserComponent} from '../../features/author-browser/components/zx-author-browser/zx-author-browser.component';
import {PicturesHomeComponent} from '../../features/catalogue-home/components/pictures-home/pictures-home.component';
import {MusicHomeComponent} from '../../features/catalogue-home/components/music-home/music-home.component';

type CollectionKind = 'prods' | 'groups' | 'pictures' | 'music' | 'authors';

interface CollectionVm {
  kind: CollectionKind;
  letter: string;
}

/**
 * Routed collection/list page (`/prods`, `/groups`, `/pictures`, `/music`,
 * `/authors`). The `kind` comes from route data; graphics and music roots mount
 * their catalogue homepages, while the remaining kinds mount their browsers.
 */
@Component({
  selector: 'zx-collection-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdsCategoryComponent,
    ZxGroupBrowserComponent,
    ZxAuthorBrowserComponent,
    PicturesHomeComponent,
    MusicHomeComponent,
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
