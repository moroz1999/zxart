import {ChangeDetectionStrategy, Component, HostBinding, Input, OnInit} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';

/**
 * Large visual/logical form section with an optional title. Use only when a form
 * has a real titled or structural block. An optional `icon` (svg asset name)
 * renders before the title in the section header. `columns` lays the section's
 * `zx-form-field` children out in a grid; when left unset the section follows the
 * parent `[zxForm] [columns]` setting.
 */
@Component({
  selector: 'zx-form-section',
  standalone: true,
  imports: [SvgIconComponent],
  templateUrl: './zx-form-section.component.html',
  styleUrl: './zx-form-section.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormSectionComponent implements OnInit {
  @Input() title = '';
  @Input() icon = '';
  @Input() columns: 1 | 2 | null = null;

  @HostBinding('class') get hostClass(): string {
    if (this.columns === 1) {
      return 'zx-form-section--cols-1';
    }
    if (this.columns === 2) {
      return 'zx-form-section--cols-2';
    }
    return '';
  }

  constructor(private readonly iconRegistry: SvgIconRegistryService) {}

  ngOnInit(): void {
    if (this.icon !== '') {
      this.iconRegistry.loadSvg(`${environment.svgUrl}${this.icon}.svg`, this.icon)?.subscribe();
    }
  }
}
