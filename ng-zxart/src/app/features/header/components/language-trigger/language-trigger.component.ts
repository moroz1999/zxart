import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {combineLatest, Observable} from 'rxjs';
import {map, shareReplay} from 'rxjs/operators';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {CurrentLanguageService} from '../../services/current-language.service';
import {LanguageItem} from '../../models/language-item';

interface LanguageVm {
  languages: LanguageItem[];
  activeLanguage: LanguageItem;
}

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
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LanguageTriggerComponent {
  /** Only emits when the server has confirmed both the language list and the active language. */
  readonly vm$: Observable<LanguageVm> = combineLatest([
    this.languageService.languages$,
    this.languageService.activeLanguage$,
  ]).pipe(
    map(([languages, activeLanguage]) => ({languages, activeLanguage})),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  popoverOpen = false;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(private languageService: CurrentLanguageService) {}

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
