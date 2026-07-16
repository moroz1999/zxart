import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';

/**
 * Routed fallback page for unmatched URLs.
 */
@Component({
  selector: 'zx-not-found',
  standalone: true,
  imports: [TranslateModule],
  templateUrl: './not-found.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class NotFoundComponent {}
