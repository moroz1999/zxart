import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ProdLanguageInfoDto} from '../../models/prod-core.dto';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

@Component({
  selector: 'zx-prod-language-links',
  standalone: true,
  imports: [CommonModule, ZxInlineComponent, TextDirective],
  templateUrl: './zx-prod-language-links.component.html',
  styleUrls: ['./zx-prod-language-links.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdLanguageLinksComponent {
  @Input() languages: ProdLanguageInfoDto[] = [];
}
