import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';

/**
 * Field-like unit with one common legend and several related controls.
 * In horizontal form layout it behaves like one form row: [legend] [nested fields].
 * `icon` is an optional svg asset name rendered before the legend text.
 */
@Component({
  selector: 'zx-form-fieldset',
  standalone: true,
  imports: [SvgIconComponent],
  templateUrl: './zx-form-fieldset.component.html',
  styleUrl: './zx-form-fieldset.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormFieldsetComponent implements OnInit {
  @Input() legend = '';
  @Input() icon = '';

  constructor(private readonly iconRegistry: SvgIconRegistryService) {}

  ngOnInit(): void {
    if (this.icon !== '') {
      this.iconRegistry.loadSvg(`${environment.svgUrl}${this.icon}.svg`, this.icon)?.subscribe();
    }
  }
}
