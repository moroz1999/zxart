import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {map, Observable} from 'rxjs';
import {ZxProdsCategoryComponent} from '../../entities/zx-prods-category/zx-prods-category.component';
import {ZxGroupBrowserComponent} from '../../features/group-browser/components/zx-group-browser/zx-group-browser.component';
import {PicturesHomeComponent} from '../../features/catalogue-home/components/pictures-home/pictures-home.component';
import {MusicHomeComponent} from '../../features/catalogue-home/components/music-home/music-home.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxEditButtonComponent} from '../../shared/ui/zx-edit-button/zx-edit-button.component';
import {CurrentUserService} from '../../shared/services/current-user.service';
import {ZxInlineComponent} from '../../shared/ui/zx-inline/zx-inline.component';

type CollectionKind = 'prods' | 'groups' | 'pictures' | 'music';

interface CollectionVm {
  kind: CollectionKind;
  letter: string;
  titleKey: string;
}

/**
 * Routed collection/list page (`/prods`, `/groups`, `/pictures`, `/music`).
 * The `kind` comes from route data; graphics and music roots mount their
 * catalogue homepages, while the remaining kinds mount their browsers.
 */
@Component({
  selector: 'zx-collection-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxProdsCategoryComponent,
    ZxGroupBrowserComponent,
    PicturesHomeComponent,
    MusicHomeComponent,
    HeadingDirective,
    ZxEditButtonComponent,
    ZxInlineComponent,
    ZxPageLayoutComponent,
  ],
  templateUrl: './collection-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CollectionPageComponent {
  readonly isAuthenticated$ = this.currentUserService.isAuthenticated$;
  readonly vm$: Observable<CollectionVm> = this.route.paramMap.pipe(
    map(params => ({
      kind: this.kind,
      letter: params.get('letter') ?? '',
      titleKey: this.titleKey,
    })),
  );

  private get kind(): CollectionKind {
    return (this.route.snapshot.data['kind'] ?? 'prods') as CollectionKind;
  }

  private get titleKey(): string {
    return (this.route.snapshot.data['titleKey'] ?? '') as string;
  }

  constructor(
    private readonly route: ActivatedRoute,
    private readonly currentUserService: CurrentUserService,
  ) {}

  groupAddUrl(vm: CollectionVm): string {
    return vm.letter ? `/groups/${encodeURIComponent(vm.letter)}/add` : '/groups/add';
  }
}
