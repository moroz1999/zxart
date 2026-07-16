import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxTagsCloudComponent} from '../../features/tags-list/components/zx-tags-cloud/zx-tags-cloud.component';

interface TagsVm {
  section: string;
  searchBasePath: string;
}

/** Routed tag-cloud entrypoint (`/pictures/tags`, `/music/tags`); section comes from route data. */
@Component({
  selector: 'zx-tags-page',
  standalone: true,
  imports: [CommonModule, ZxTagsCloudComponent],
  template: `
    <zx-tags-cloud
      *ngIf="vm$ | async as vm"
      [section]="vm.section"
      [searchBasePath]="vm.searchBasePath"
    ></zx-tags-cloud>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TagsPageComponent {
  readonly vm$: Observable<TagsVm> = this.route.data.pipe(
    map(data => ({
      section: (data['section'] ?? 'graphics') as string,
      searchBasePath: (data['searchBasePath'] ?? '/pictures/search') as string,
    })),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
