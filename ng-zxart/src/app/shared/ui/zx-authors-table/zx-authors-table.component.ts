import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {AuthorListItem} from '../../../features/author-browser/models/author-list-item';
import {ZxTableComponent} from '../zx-table/zx-table.component';

@Component({
  selector: 'zx-authors-table',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
  ],
  templateUrl: './zx-authors-table.component.html',
  styleUrls: ['./zx-authors-table.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorsTableComponent {
  @Input() authors: AuthorListItem[] = [];
  @Input() rowStartIndex = 0;
  @Input() loading = false;
  @Input() showRowNumbers = true;
}
