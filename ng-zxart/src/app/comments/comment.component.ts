import {Component, Input} from '@angular/core';
import {CommentDto} from '../shared/services/comments.service';

@Component({
  selector: 'app-comment',
  standalone: false,
  template: `
    <div class="comment">
      <div class="comment-header">
        <a *ngIf="comment.authorUrl; else noUrl" [href]="comment.authorUrl" class="comment-author">
          {{ comment.author }}
        </a>
        <ng-template #noUrl>
          <span class="comment-author">{{ comment.author }}</span>
        </ng-template>
        <span *ngIf="comment.authorBadge" class="comment-badge">{{ comment.authorBadge }}</span>
        <span class="comment-date">{{ comment.date }}</span>
      </div>
      <div class="comment-content" [innerHTML]="comment.content"></div>
      <div class="comment-footer">
        <span class="comment-votes">Рейтинг: {{ comment.votes }}</span>
      </div>
    </div>
  `,
  styles: [`
    .comment {
      border: 1px solid #ddd;
      padding: 10px;
      margin-bottom: 5px;
      background-color: #f9f9f9;
    }
    .comment-header {
      margin-bottom: 5px;
      font-size: 0.9em;
    }
    .comment-author {
      font-weight: bold;
      margin-right: 10px;
    }
    .comment-badge {
      background-color: #eee;
      padding: 2px 5px;
      border-radius: 3px;
      margin-right: 10px;
    }
    .comment-date {
      color: #666;
    }
    .comment-content {
      margin-bottom: 5px;
    }
    .comment-footer {
      font-size: 0.8em;
      color: #888;
    }
  `]
})
export class CommentComponent {
  @Input() comment!: CommentDto;
}
