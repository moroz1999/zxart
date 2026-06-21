import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {NgFor, NgIf} from '@angular/common';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';

/** A single label → value row of technical settings. Both strings are pre-resolved. */
export interface TechSettingRow {
  readonly label: string;
  readonly value: string;
}

/**
 * Collapsible "technical settings" widget: a toggle button revealing a
 * key/value list with monospaced values. Domain-agnostic — callers resolve
 * their own labels and values and pass them as {@link TechSettingRow}s.
 */
@Component({
  selector: 'zx-tech-settings',
  standalone: true,
  imports: [NgFor, NgIf, SvgIconComponent],
  templateUrl: './zx-tech-settings.component.html',
  styleUrl: './zx-tech-settings.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTechSettingsComponent implements OnInit {
  @Input({required: true}) title!: string;
  @Input() rows: TechSettingRow[] = [];
  @Input() open = false;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}expand-more.svg`, 'expand-more')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}expand-less.svg`, 'expand-less')?.subscribe();
  }

  toggle(): void {
    this.open = !this.open;
  }
}
