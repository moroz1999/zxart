import {ChangeDetectionStrategy, Component, OnDestroy, OnInit} from '@angular/core';
import {Router, RouterOutlet} from '@angular/router';
import {Subscription} from 'rxjs';
import {LanguageService} from './features/settings/services/language.service';
import {ZxHeaderComponent} from './features/header/components/zx-header/zx-header.component';
import {ZxRightColumnComponent} from './features/header/components/zx-right-column/zx-right-column.component';
import {ZxBreadcrumbBarComponent} from './shared/ui/zx-breadcrumb-bar/zx-breadcrumb-bar.component';
import {PlayerHostComponent} from './features/player/components/player-host/player-host.component';
import {AnalyticsService} from './shared/services/analytics.service';
import {PageMetadataService} from './shared/services/page-metadata.service';

/**
 * Root component of the routed SPA. Bootstrapped into the `<app-root>` tag
 * served by the PHP SPA shell (index.spa.tpl), it owns the entire page chrome:
 * header, the columns layout (routed content + right column), and the player.
 * The shell contains no other custom elements — everything lives inside the app.
 *
 * Router initial navigation is disabled at the module level so the bundle, when
 * loaded on a still-legacy page, never auto-navigates. Navigation starts here,
 * only once this root is actually bootstrapped.
 */
@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, ZxHeaderComponent, ZxRightColumnComponent, ZxBreadcrumbBarComponent, PlayerHostComponent],
  templateUrl: './spa-root.component.html',
  styleUrls: ['./spa-root.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SpaRootComponent implements OnInit, OnDestroy {
  private readonly subscription = new Subscription();

  constructor(
    private readonly router: Router,
    private readonly languageService: LanguageService,
    private readonly pageMetadataService: PageMetadataService,
    private readonly analyticsService: AnalyticsService,
  ) {}

  ngOnInit(): void {
    // Set up the interface language (stored preference → English) before the
    // first navigation so translations and the language header are ready; the
    // logged-in user's stored language is then applied once preferences load.
    this.languageService.initialize();
    this.analyticsService.initialize();
    this.subscription.add(this.pageMetadataService.metadata$.subscribe(() => {
      this.analyticsService.trackPageView(this.router.url);
    }));
    this.router.initialNavigation();
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }

  onOutletActivate(): void {
    if (this.router.getCurrentNavigation()?.trigger === 'imperative') {
      requestAnimationFrame(() => window.scrollTo({top: 0, behavior: 'auto'}));
    }
  }
}
