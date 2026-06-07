import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {PressArticlePreviewDto} from '../../../prod-details/models/press-article.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ZxPressMentionsListComponent} from '../../../../entities/zx-press-mentions-list/zx-press-mentions-list.component';

@Component({
  selector: 'zx-group-mentions',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxStackComponent, HeadingDirective, ZxPressMentionsListComponent],
  templateUrl: './zx-group-mentions.component.html',
  styleUrl: './zx-group-mentions.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupMentionsComponent {
  @Input() mentions: PressArticlePreviewDto[] = [];
}
