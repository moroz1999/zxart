import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {Router, RouterOutlet} from '@angular/router';
import {LanguageService} from './features/settings/services/language.service';
import {ZxHeaderComponent} from './features/header/components/zx-header/zx-header.component';
import {ZxRightColumnComponent} from './features/header/components/zx-right-column/zx-right-column.component';
import {PlayerHostComponent} from './features/player/components/player-host/player-host.component';

/**
 * Root component of the routed SPA. Bootstrapped into the `<zx-spa-root>` tag
 * served by the PHP SPA shell (index.spa.tpl), it owns the entire page chrome:
 * header, the columns layout (routed content + right column), and the player.
 * The shell contains no other custom elements — everything lives inside the app.
 *
 * Router initial navigation is disabled at the module level so the bundle, when
 * loaded on a still-legacy page, never auto-navigates. Navigation starts here,
 * only once this root is actually bootstrapped.
 */
@Component({
  selector: 'zx-spa-root',
  standalone: true,
  imports: [RouterOutlet, ZxHeaderComponent, ZxRightColumnComponent, PlayerHostComponent],
  templateUrl: './spa-root.component.html',
  styleUrls: ['./spa-root.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SpaRootComponent implements OnInit {
  constructor(
    private readonly router: Router,
    private readonly languageService: LanguageService,
  ) {}

  ngOnInit(): void {
    // Set up the interface language (stored preference → English) before the
    // first navigation so translations and the language header are ready; the
    // logged-in user's stored language is then applied once preferences load.
    this.languageService.initialize();
    this.router.initialNavigation();
  }
}
