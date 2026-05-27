import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {BackendLinksService} from '../../../features/header/services/backend-links.service';

export interface BreadcrumbItemDto {
  title: string;
  url: string;
}

@Component({
  selector: 'zx-breadcrumbs',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-breadcrumbs.component.html',
  styleUrl: './zx-breadcrumbs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbsComponent {
  @Input() categories: BreadcrumbItemDto[] = [];
  @Input() parentItem: { title: string; url: string } | null = null;
  @Input() currentTitle = '';

  readonly homeUrl$: Observable<string | null> = this.backendLinks.links$.pipe(
    map(l => l.homeUrl),
  );

  constructor(private readonly backendLinks: BackendLinksService) {}
}
