import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdLanguageInfoDto} from '../../models/prod-core.dto';

@Component({
  selector: 'zx-prod-language-links',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-prod-language-links.component.html',
  styleUrls: ['./zx-prod-language-links.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdLanguageLinksComponent {
  @Input() languages: ProdLanguageInfoDto[] = [];
}
