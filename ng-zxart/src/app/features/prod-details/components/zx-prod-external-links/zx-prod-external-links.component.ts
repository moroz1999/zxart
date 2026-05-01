import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdLinkInfoDto} from '../../models/prod-core.dto';

@Component({
  selector: 'zx-prod-external-links',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-prod-external-links.component.html',
  styleUrls: ['./zx-prod-external-links.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdExternalLinksComponent {
  @Input() links: ProdLinkInfoDto[] = [];
}
