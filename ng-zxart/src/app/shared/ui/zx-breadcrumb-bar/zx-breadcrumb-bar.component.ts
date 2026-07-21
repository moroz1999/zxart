import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxBreadcrumbsComponent} from '../zx-breadcrumbs/zx-breadcrumbs.component';
import {BreadcrumbService} from '../../services/breadcrumb.service';

/**
 * Site-wide breadcrumb bar. Rendered once in the shell, above every page's
 * `<h1>`, it reflects {@link BreadcrumbService}'s current trail and mirrors the
 * top menu structure. Renders nothing on the home page or before an entity page
 * has published its trail.
 */
@Component({
  selector: 'zx-breadcrumb-bar',
  standalone: true,
  imports: [CommonModule, ZxBreadcrumbsComponent],
  template: `
    <ng-container *ngIf="trail$ | async as trail">
      <zx-breadcrumbs
        *ngIf="trail.items.length || trail.currentTitle"
        class="zx-breadcrumb-bar"
        [categories]="trail.items"
        [currentTitle]="trail.currentTitle">
      </zx-breadcrumbs>
    </ng-container>
  `,
  styleUrl: './zx-breadcrumb-bar.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbBarComponent {
  readonly trail$ = this.breadcrumbService.trail$;

  constructor(private readonly breadcrumbService: BreadcrumbService) {}
}
