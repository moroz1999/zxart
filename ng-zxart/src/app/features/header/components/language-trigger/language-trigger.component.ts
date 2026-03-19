import {Component, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {LanguagesService} from '../../services/languages.service';
import {CurrentRouteService} from '../../services/current-route.service';
import {LanguageItem} from '../../models/language-item';

@Component({
  selector: 'zx-language-trigger',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    ZxButtonComponent,
    ZxHeaderPopoverComponent,
    ZxPopoverMenuItemComponent,
  ],
  templateUrl: './language-trigger.component.html',
  styleUrls: ['./language-trigger.component.scss'],
})
export class LanguageTriggerComponent implements OnInit {
  languages: LanguageItem[] = [];
  activeLanguage: LanguageItem | null = null;
  popoverOpen = false;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(
    private languagesService: LanguagesService,
    private routeService: CurrentRouteService,
  ) {}

  ngOnInit(): void {
    this.languagesService.getLanguages(this.routeService.pathname).subscribe(languages => {
      this.languages = languages;
      this.activeLanguage = languages.find(l => l.active) ?? languages[0] ?? null;
    });
  }

  togglePopover(event: Event): void {
    event.stopPropagation();
    this.popoverOpen = !this.popoverOpen;
  }

  closePopover(): void {
    this.popoverOpen = false;
  }

  onOverlayKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
      this.closePopover();
    }
  }
}
