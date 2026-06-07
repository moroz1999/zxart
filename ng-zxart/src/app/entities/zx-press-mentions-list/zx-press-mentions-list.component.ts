import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {
  ZxArticlePreviewAuthor,
  ZxArticlePreviewComponent,
  ZxArticlePreviewPublication,
} from '../zx-article-preview/zx-article-preview.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

/**
 * A press-article entry rendered by the shared press list. Structurally compatible with the
 * prod/group `PressArticlePreviewDto`, so feature payloads can be passed without mapping.
 */
export interface PressMentionItem {
  id: number;
  title: string;
  url: string;
  introduction: string;
  authors: ZxArticlePreviewAuthor[];
  publication: ZxArticlePreviewPublication | null;
}

/**
 * Shared list of press-article cards, reused by prod and group press mentions so both render
 * the exact same list. The host owns the surrounding heading/panel and lazy loading.
 */
@Component({
  selector: 'zx-press-mentions-list',
  standalone: true,
  imports: [CommonModule, ZxArticlePreviewComponent, ZxStackComponent],
  templateUrl: './zx-press-mentions-list.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPressMentionsListComponent {
  @Input() articles: PressMentionItem[] = [];
  @Input() readLinkLabel: string | null = null;

  trackById(_index: number, article: PressMentionItem): number {
    return article.id;
  }
}
