import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxPressDetailsComponent} from '../../features/press-details/components/zx-press-details/zx-press-details.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';

/** Routed page for `press/:id`. */
@Component({
  selector: 'zx-press-page',
  standalone: true,
  imports: [CommonModule, ZxPressDetailsComponent, HeadingDirective, ZxPageLayoutComponent],
  templateUrl: './press-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PressPageComponent {
  title = '';

  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
