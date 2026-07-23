import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {NgIf} from '@angular/common';
import {RouterLink} from '@angular/router';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';

/** A place reference rendered as a link inside `zx-location`. */
export interface ZxLocationPlaceDto {
  readonly title: string;
  readonly url: string;
}

/** Pin icon followed by the linked city and country of an entity. */
@Component({
  selector: 'zx-location',
  standalone: true,
  imports: [NgIf, RouterLink, SvgIconComponent],
  templateUrl: './zx-location.component.html',
  styleUrl: './zx-location.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxLocationComponent implements OnInit {
  @Input() city: ZxLocationPlaceDto | null = null;
  @Input() country: ZxLocationPlaceDto | null = null;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}location.svg`, 'location')?.subscribe();
  }
}
