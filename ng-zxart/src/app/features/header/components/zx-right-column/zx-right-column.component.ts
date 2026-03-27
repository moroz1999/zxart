import {afterNextRender, ChangeDetectionStrategy, Component, inject, Inject, Renderer2} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {BreakpointObserver} from '@angular/cdk/layout';
import {toSignal} from '@angular/core/rxjs-interop';
import {map} from 'rxjs/operators';
import {DOCUMENT} from '@angular/common';
import {ZxBreakpoints} from '../../../../shared/breakpoints';
import {CurrentUserService} from '../../../../shared/services/current-user.service';
import {BackendLinksService} from '../../services/backend-links.service';
import {RadioRemoteComponent} from '../../../radio-remote/components/radio-remote/radio-remote.component';
import {LatestCommentsComponent} from '../../../comments/components/latest-comments/latest-comments.component';
import {
  RecentRatingsWidgetComponent
} from '../../../ratings/components/recent-ratings-widget/recent-ratings-widget.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';

@Component({
  selector: 'zx-right-column',
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
  private readonly currentUserService = inject(CurrentUserService);
  private readonly backendLinksService = inject(BackendLinksService);
  private readonly renderer = inject(Renderer2);

  readonly isDesktop = toSignal(
    this.bp.observe(ZxBreakpoints.Desktop).pipe(map(s => s.matches)),
    {requireSync: true},
  );
  readonly user = toSignal(this.currentUserService.user$);
  readonly supportUrl = toSignal(
    this.backendLinksService.links$.pipe(map(l => l.supportUrl)),
    {initialValue: null},
  );

  constructor(@Inject(DOCUMENT) private readonly doc: Document) {
    afterNextRender(() => this.initAds());
  }

  private initAds(): void {
    const user = this.user();
    if (!user?.hasAds || !this.isDesktop()) {
      return;
    }
    if (!this.doc.querySelector('script[src*="adsbygoogle"]')) {
      const script = this.renderer.createElement('script') as HTMLScriptElement;
      script.async = true;
      script.src =
        'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6845376753120137';
      script.setAttribute('crossorigin', 'anonymous');
      this.renderer.appendChild(this.doc.head, script);
    }
    ((window as any).adsbygoogle = (window as any).adsbygoogle || []).push({});
  }
}
