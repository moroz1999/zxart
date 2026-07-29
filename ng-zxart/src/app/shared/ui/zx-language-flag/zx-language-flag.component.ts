import {NgIf} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';

/** Flags already pulled into the registry, so each one is fetched once per session. */
const LOADED = new Set<string>();

/**
 * Flag of a software language, drawn as a flat SVG (`assets/svg/flags/<code>.svg`).
 * Replaces the flag emoji, which several platforms — Windows above all — do not
 * render at all. An unknown code renders nothing rather than a broken icon.
 */
@Component({
  selector: 'zx-language-flag',
  standalone: true,
  imports: [NgIf, SvgIconComponent],
  templateUrl: './zx-language-flag.component.html',
  styleUrl: './zx-language-flag.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxLanguageFlagComponent implements OnChanges {
  @Input({required: true}) code!: string;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  get iconName(): string {
    return `flag-${this.code}`;
  }

  ngOnChanges(): void {
    const name = this.iconName;
    if (this.code && !LOADED.has(name)) {
      LOADED.add(name);
      this.iconReg.loadSvg(`${environment.svgUrl}flags/${this.code}.svg`, name)?.subscribe();
    }
  }
}
