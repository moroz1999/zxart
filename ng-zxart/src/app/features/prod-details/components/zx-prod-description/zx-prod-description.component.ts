import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxCollapsibleSectionComponent} from '../../../../shared/ui/zx-collapsible-section/zx-collapsible-section.component';

@Component({
  selector: 'zx-prod-description',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxCollapsibleSectionComponent],
  templateUrl: './zx-prod-description.component.html',
  styleUrls: ['./zx-prod-description.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDescriptionComponent {
  @Input({required: true}) description!: string;
  @Input() htmlDescription = false;
}
