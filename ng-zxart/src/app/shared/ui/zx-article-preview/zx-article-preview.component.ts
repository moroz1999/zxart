import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxBodySmMutedDirective, ZxBodyStrongDirective,} from '../../directives/typography/typography.directives';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';

export interface ZxArticlePreviewAuthor {
  readonly title: string;
  readonly url: string;
}

export interface ZxArticlePreviewPublication {
  readonly title: string;
  readonly url: string;
  readonly year: number | null;
  readonly imageUrl: string | null;
}

@Component({
  selector: 'zx-article-preview',
  standalone: true,
  imports: [
    CommonModule,
    ZxBodyStrongDirective,
    ZxBodySmMutedDirective,
    ZxButtonComponent,
    ZxPanelComponent,
  ],
  templateUrl: './zx-article-preview.component.html',
  styleUrls: ['./zx-article-preview.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxArticlePreviewComponent {
  @Input() title = '';
  @Input() titleHtml: string | null = null;
  @Input() url = '';
  @Input() snippetHtml: string | null = null;
  @Input() year: number | null = null;
  @Input() imageUrl: string | null = null;
  @Input() imageAlt = '';
  @Input() authors: ZxArticlePreviewAuthor[] = [];
  @Input() typeLabel: string | null = null;
  @Input() readLinkLabel: string | null = null;
  @Input() publication: ZxArticlePreviewPublication | null = null;
}
