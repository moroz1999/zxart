import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {environment} from '../../../../environments/environment';

/**
 * Download button used across hero action bars: a `zx-button` link with a
 * download icon and a label. Owns the icon sizing so every hero download button
 * looks the same.
 */
@Component({
  selector: 'zx-download-button',
  standalone: true,
  imports: [SvgIconComponent, ZxButtonComponent],
  templateUrl: './zx-download-button.component.html',
  styleUrl: './zx-download-button.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxDownloadButtonComponent implements OnInit {
  @Input({required: true}) href!: string;
  @Input() label = '';
  @Input() color: 'secondary' | 'outlined' = 'outlined';
  @Input() size: 'xs' | 'sm' | 'md' = 'sm';
  @Input() rel: string | null = null;
  @Input() target: '_self' | '_blank' | '_parent' | '_top' | null = null;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }
}
