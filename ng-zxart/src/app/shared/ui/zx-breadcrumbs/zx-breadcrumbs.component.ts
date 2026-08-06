import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';

export interface BreadcrumbItemDto {
  title: string;
  id?: number;
  url?: string;
  /** Query params for the SPA routerLink when `url` is an internal path. */
  queryParams?: Record<string, string | number>;
}

@Component({
  selector: 'zx-breadcrumbs',
  standalone: true,
  imports: [RouterLink, CommonModule],
  templateUrl: './zx-breadcrumbs.component.html',
  styleUrl: './zx-breadcrumbs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbsComponent {
  @Input() categories: BreadcrumbItemDto[] = [];
  @Input() categoryCataloguePath: string | null = null;
  @Input() parentItem: { title: string; url: string } | null = null;
  @Input() currentTitle = '';
}
