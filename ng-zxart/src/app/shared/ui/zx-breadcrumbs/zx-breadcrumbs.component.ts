import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, of} from 'rxjs';
import {map} from 'rxjs/operators';
import {BackendLinksService} from '../../../features/header/services/backend-links.service';
import {ProdCategoryRefDto} from '../../../features/prod-details/models/prod-core.dto';

@Component({
  selector: 'zx-breadcrumbs',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-breadcrumbs.component.html',
  styleUrl: './zx-breadcrumbs.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxBreadcrumbsComponent {
  @Input() categories: ProdCategoryRefDto[] = [];
  @Input() currentTitle = '';

  readonly homeUrl$: Observable<string | null> = this.backendLinks.links$.pipe(
    map(l => l.homeUrl),
  );

  constructor(private readonly backendLinks: BackendLinksService) {}
}
