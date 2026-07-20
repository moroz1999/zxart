import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {TranslateModule} from '@ngx-translate/core';
import {ZxTagsCloudComponent} from '../../features/tags-list/components/zx-tags-cloud/zx-tags-cloud.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

interface TagsVm {
  section: string;
  searchBasePath: string;
  titleKey: string;
}

/** Routed tag-cloud entrypoint (`/pictures/tags`, `/music/tags`); section comes from route data. */
@Component({
  selector: 'zx-tags-page',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxTagsCloudComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './tags-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TagsPageComponent {
  readonly vm$: Observable<TagsVm> = this.route.data.pipe(
    map(data => ({
      section: (data['section'] ?? 'graphics') as string,
      searchBasePath: (data['searchBasePath'] ?? '/pictures/search') as string,
      titleKey: (data['titleKey'] ?? 'menu.gfx.tags') as string,
    })),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
