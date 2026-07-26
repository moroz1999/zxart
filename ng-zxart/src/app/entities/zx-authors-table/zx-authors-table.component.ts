import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {AuthorListItem} from '../../features/author-browser/models/author-list-item';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';
import {RouterLink} from '@angular/router';
import {ZxLoadingStateDirective} from '../../shared/ui/zx-loading-state/zx-loading-state.directive';

@Component({
  selector: 'zx-authors-table',
  standalone: true,
  imports: [
    RouterLink,
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxLoadingStateDirective,
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
