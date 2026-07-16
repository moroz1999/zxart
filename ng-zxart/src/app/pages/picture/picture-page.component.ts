import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ActivatedRoute} from '@angular/router';
import {map, Observable} from 'rxjs';
import {ZxPictureDetailsComponent} from '../../features/picture-details/components/zx-picture-details/zx-picture-details.component';

/** Routed page for `picture/:id`. */
@Component({
  selector: 'zx-picture-page',
  standalone: true,
  imports: [CommonModule, ZxPictureDetailsComponent],
  templateUrl: './picture-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PicturePageComponent {
  readonly elementId$: Observable<number> = this.route.paramMap.pipe(
    map(params => Number(params.get('id')) || 0),
  );

  constructor(private readonly route: ActivatedRoute) {}
}
