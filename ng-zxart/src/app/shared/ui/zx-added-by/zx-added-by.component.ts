import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';

export interface AddedBySubmitterDto {
  userName: string;
  url: string;
}

@Component({
  selector: 'zx-added-by',
  standalone: true,
  imports: [CommonModule, TranslateModule],
  templateUrl: './zx-added-by.component.html',
  styleUrls: ['./zx-added-by.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAddedByComponent {
  @Input({required: true}) dateAdded!: string;
  @Input() submitter: AddedBySubmitterDto | null = null;

  @HostBinding('class.app-typography-label') readonly labelClass = true;
  @HostBinding('class.app-typography-tone-muted') readonly mutedClass = true;
}
