import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';
import {
  ZxSkeletonBoneComponent,
} from '../zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';

export interface BreadcrumbItemDto {
  title: string;
  id?: number;
  url?: string;
}

@Component({
  selector: 'zx-breadcrumbs',
  standalone: true,
  imports: [RouterLink, CommonModule, ZxSkeletonBoneComponent],
  templateUrl: './zx-breadcrumbs.component.html',
  styleUrl: './zx-breadcrumbs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbsComponent {
  @Input() categories: BreadcrumbItemDto[] = [];
  @Input() categoryCataloguePath: string | null = null;
  @Input() parentItem: { title: string; url: string } | null = null;
  @Input() currentTitle = '';
  @Input() loading = false;

  readonly skeletonItems = [
    {id: 'home', delayMs: 0, label: 'ZX-Art'},
    {id: 'category', delayMs: 40, label: 'Category'},
    {id: 'parent', delayMs: 80, label: 'Parent item'},
    {id: 'current', delayMs: 120, label: 'Current page'},
  ];

}
