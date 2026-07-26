import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {NgIf} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ProdContextDto} from '../../models/prod-context.dto';
import {RouterLink} from '@angular/router';
/**
 * Provenance line linking an item back to the prod/release it is part of:
 * "From release «title» · year". Shared by the picture and tune detail pages.
 */
@Component({
  selector: 'zx-prod-context',
  standalone: true,
  imports: [RouterLink, NgIf, TranslateModule, TextDirective],
  templateUrl: './zx-prod-context.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdContextComponent {
  @Input({required: true}) context!: ProdContextDto;
}
