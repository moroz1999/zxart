import {Component, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CdkConnectedOverlay, CdkOverlayOrigin, ConnectedPosition} from '@angular/cdk/overlay';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {LanguagesService} from '../../services/languages.service';
import {LanguageItem} from '../../models/language-item';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-language-trigger',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    CdkConnectedOverlay,
    CdkOverlayOrigin,
    SvgIconComponent,
    ZxButtonComponent,
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
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}globe.svg`, 'globe')?.subscribe();
    const path = window.location.pathname;
    this.languagesService.getLanguages(path).subscribe(languages => {
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
