import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';

export interface ZxProdFileListItem {
  readonly id: number;
  readonly title: string;
  readonly fileName: string;
  readonly downloadUrl: string;
  readonly author: string | null;
}

@Component({
  selector: 'zx-prod-files-list',
  standalone: true,
  imports: [CommonModule, TextDirective],
  templateUrl: './zx-prod-files-list.component.html',
  styleUrls: ['./zx-prod-files-list.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdFilesListComponent {
  @Input({required: true}) files: ZxProdFileListItem[] = [];
  @Input() newWindow = false;
}
