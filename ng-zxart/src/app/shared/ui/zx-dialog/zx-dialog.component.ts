import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {HeadingDirective} from '../typography/directives/heading.directive';
import {ZxCloseButtonComponent} from '../zx-close-button/zx-close-button.component';

@Component({
  selector: 'zx-dialog',
  standalone: true,
  imports: [CommonModule, TranslateModule, HeadingDirective, ZxCloseButtonComponent],
  templateUrl: './zx-dialog.component.html',
  styleUrl: './zx-dialog.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxDialogComponent {
  @Input() titleKey?: string;
  @Input() title?: string;
  @Input() showClose = true;
  @Input() customHeader = false;
  @Output() closeClick = new EventEmitter<void>();
}
