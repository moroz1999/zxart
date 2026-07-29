import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxHeaderPopoverComponent} from '../../../../shared/ui/zx-header-popover/zx-header-popover.component';
import {LanguageService} from '../../../settings/services/language.service';
import {LanguageOption} from '../../../settings/models/language-option';
import {ZxLanguageFlagComponent} from '../../../../shared/ui/zx-language-flag/zx-language-flag.component';

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
    ZxLanguageFlagComponent,
  ],
  templateUrl: './language-trigger.component.html',
  styleUrls: ['./language-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LanguageTriggerComponent {
  readonly languages: readonly LanguageOption[] = this.languageService.languages;

  /** The currently active language, derived from the frontend language state. */
  readonly active$: Observable<LanguageOption> = this.languageService.current$.pipe(
    map(short => this.languages.find(language => language.short === short) ?? this.languages[0]),
  );

  popoverOpen = false;

  readonly positions: ConnectedPosition[] = [
    {originX: 'end', originY: 'bottom', overlayX: 'end', overlayY: 'top', offsetY: 4},
    {originX: 'end', originY: 'top', overlayX: 'end', overlayY: 'bottom', offsetY: -4},
  ];

  constructor(private languageService: LanguageService) {}

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

  choose(language: LanguageOption): void {
    this.languageService.setLanguage(language.short);
    this.closePopover();
  }
}
