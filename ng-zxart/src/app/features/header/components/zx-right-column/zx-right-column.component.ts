import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {BreakpointObserver} from '@angular/cdk/layout';
import {toSignal} from '@angular/core/rxjs-interop';
import {map} from 'rxjs/operators';
import {ZxBreakpoints} from '../../../../shared/breakpoints';
import {RadioRemoteComponent} from '../../../radio-remote/components/radio-remote/radio-remote.component';
import {LatestCommentsComponent} from '../../../comments/components/latest-comments/latest-comments.component';
import {
  RecentRatingsWidgetComponent
} from '../../../ratings/components/recent-ratings-widget/recent-ratings-widget.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';

@Component({
  selector: 'zx-right-column, zx-right-column-view',
  standalone: true,
  imports: [
    TranslateModule,
    RadioRemoteComponent,
    LatestCommentsComponent,
    RecentRatingsWidgetComponent,
    ZxButtonComponent,
    ZxPanelComponent,
  ],
  templateUrl: './zx-right-column.component.html',
  styleUrls: ['./zx-right-column.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxRightColumnComponent {
  private readonly bp = inject(BreakpointObserver);

  readonly isDesktop = toSignal(
    this.bp.observe(ZxBreakpoints.Desktop).pipe(map(s => s.matches)),
    {requireSync: true},
  );
}
