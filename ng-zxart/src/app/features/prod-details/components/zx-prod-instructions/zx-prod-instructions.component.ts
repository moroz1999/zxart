import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxCollapsibleSectionComponent} from '../../../../shared/ui/zx-collapsible-section/zx-collapsible-section.component';

@Component({
  selector: 'zx-prod-instructions',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxCollapsibleSectionComponent],
  templateUrl: './zx-prod-instructions.component.html',
  styleUrls: ['./zx-prod-instructions.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInstructionsComponent {
  @Input({required: true}) instructions!: string;
}
