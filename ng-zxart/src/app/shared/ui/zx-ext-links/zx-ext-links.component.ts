import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {NgFor} from '@angular/common';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {TextDirective} from '../typography/directives/text.directive';

/** An outbound link rendered by `zx-ext-links`. */
export interface ZxExtLinkDto {
  readonly url: string;
  readonly label: string;
}

/**
 * The single rendering for outbound links in hero blocks: label plus a trailing
 * "open in new" icon. Always used inside a `zx-meta-row`.
 */
@Component({
  selector: 'zx-ext-links',
  standalone: true,
  imports: [NgFor, SvgIconComponent, TextDirective],
  templateUrl: './zx-ext-links.component.html',
  styleUrl: './zx-ext-links.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxExtLinksComponent implements OnInit {
  @Input() links: ZxExtLinkDto[] = [];

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}open-in-new.svg`, 'open-in-new')?.subscribe();
  }

  trackByUrl(_index: number, link: ZxExtLinkDto): string {
    return link.url;
  }
}
