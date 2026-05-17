import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {HeadingDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-prod-instructions',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeadingDirective, ZxStackComponent],
  templateUrl: './zx-prod-instructions.component.html',
  styleUrls: ['./zx-prod-instructions.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInstructionsComponent {
  @Input({required: true}) instructions!: string;
}
