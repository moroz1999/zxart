import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {BackendLinksService} from '../../../features/header/services/backend-links.service';
import {
  ZxSkeletonBoneComponent,
} from '../zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';

export interface BreadcrumbItemDto {
  title: string;
  url: string;
}

@Component({
  selector: 'zx-breadcrumbs',
  standalone: true,
  imports: [CommonModule, ZxSkeletonBoneComponent],
  templateUrl: './zx-breadcrumbs.component.html',
  styleUrl: './zx-breadcrumbs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbsComponent {
  @Input() categories: BreadcrumbItemDto[] = [];
  @Input() parentItem: { title: string; url: string } | null = null;
  @Input() currentTitle = '';
  @Input() loading = false;

  readonly homeUrl$: Observable<string | null> = this.backendLinks.links$.pipe(
    map(l => l.homeUrl),
  );

  readonly skeletonItems = [
    {id: 'home', delayMs: 0, label: 'ZX-Art'},
    {id: 'category', delayMs: 40, label: 'Category'},
    {id: 'parent', delayMs: 80, label: 'Parent item'},
    {id: 'current', delayMs: 120, label: 'Current page'},
  ];

  constructor(private readonly backendLinks: BackendLinksService) {}
}
