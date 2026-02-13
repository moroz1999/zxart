import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

export type ZxSpinnerSize = 'xs' | 'sm' | 'md' | 'lg' | 'xl';

@Component({
  selector: 'zx-spinner',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-spinner.component.html',
  styleUrls: ['./zx-spinner.component.scss'],
})
export class ZxSpinnerComponent {
  @Input() size: ZxSpinnerSize = 'md';
}
