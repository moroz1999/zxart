import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxReleaseDetailsComponent} from '../../features/release-details/components/zx-release-details/zx-release-details.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed page for `release/:id`. */
@Component({
  selector: 'zx-release-page',
  standalone: true,
  imports: [CommonModule, ZxReleaseDetailsComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './release-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ReleasePageComponent {
  title = '';

  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
