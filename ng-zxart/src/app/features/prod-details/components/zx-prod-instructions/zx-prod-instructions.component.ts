import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

@Component({
  selector: 'zx-prod-instructions',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-prod-instructions.component.html',
  styleUrls: ['./zx-prod-instructions.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInstructionsComponent {
  @Input({required: true}) instructions!: string;
}
