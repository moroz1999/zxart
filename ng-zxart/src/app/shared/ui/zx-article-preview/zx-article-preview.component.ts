import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {
  ZxBodyDirective,
  ZxBodySmMutedDirective,
  ZxHeading3Directive,
} from '../../directives/typography/typography.directives';
import {ZxButtonComponent} from '../zx-button/zx-button.component';

export interface ZxArticlePreviewAuthor {
  readonly title: string;
  readonly url: string;
}

@Component({
  selector: 'zx-article-preview',
  standalone: true,
  imports: [
    CommonModule,
    ZxHeading3Directive,
    ZxBodyDirective,
    ZxBodySmMutedDirective,
    ZxButtonComponent,
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
  @Input() authors: ZxArticlePreviewAuthor[] = [];
  @Input() typeLabel: string | null = null;
  @Input() readLinkLabel: string | null = null;
}
