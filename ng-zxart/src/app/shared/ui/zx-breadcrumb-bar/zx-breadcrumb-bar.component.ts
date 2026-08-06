import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxBreadcrumbsComponent} from '../zx-breadcrumbs/zx-breadcrumbs.component';
import {
  ZxBreadcrumbsSkeletonComponent,
} from '../zx-breadcrumbs-skeleton/zx-breadcrumbs-skeleton.component';
import {BreadcrumbService} from '../../services/breadcrumb.service';

/**
 * Site-wide breadcrumb bar. Rendered once in the shell, above every page's
 * `<h1>`, it reflects {@link BreadcrumbService}'s current state and mirrors the
 * top menu structure.
 *
 * While the trail of the current page is still being resolved the bar shows
 * `zx-breadcrumbs-skeleton`, which takes exactly the row the trail will take,
 * so the page below does not move when the trail arrives. Only the home page,
 * which has no breadcrumbs, renders nothing.
 */
@Component({
  selector: 'zx-breadcrumb-bar',
  standalone: true,
  imports: [CommonModule, ZxBreadcrumbsComponent, ZxBreadcrumbsSkeletonComponent],
  template: `
    <ng-container *ngIf="state$ | async as state">
      <zx-breadcrumbs-skeleton *ngIf="state.loading; else trail" class="zx-breadcrumb-bar">
      </zx-breadcrumbs-skeleton>
      <ng-template #trail>
        <zx-breadcrumbs
          *ngIf="state.items.length || state.currentTitle"
          class="zx-breadcrumb-bar"
          [categories]="state.items"
          [currentTitle]="state.currentTitle">
        </zx-breadcrumbs>
      </ng-template>
    </ng-container>
  `,
  styleUrl: './zx-breadcrumb-bar.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbBarComponent {
  readonly state$ = this.breadcrumbService.state$;

  constructor(private readonly breadcrumbService: BreadcrumbService) {}
}
