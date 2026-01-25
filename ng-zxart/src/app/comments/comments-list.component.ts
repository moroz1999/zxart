import {Component, Input, OnInit} from '@angular/core';
import {CommentDto, CommentsService} from '../shared/services/comments.service';

@Component({
  selector: 'app-comments-list',
  standalone: false,
  template: `
    <div class="comments-list">
      <h3 *ngIf="isRoot && comments.length > 0">Комментарии</h3>
      <div *ngFor="let comment of comments" class="comment-item" [class.root-comment]="isRoot">
        <app-comment [comment]="comment"></app-comment>
        <div class="comment-children" *ngIf="comment.children && comment.children.length > 0">
          <app-comments-list [comments]="comment.children" [isRoot]="false"></app-comments-list>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .comment-children {
      margin-left: 20px;
      border-left: 1px solid #eee;
      padding-left: 10px;
    }
    .root-comment {
      margin-bottom: 20px;
    }
  `]
})
export class CommentsListComponent implements OnInit {
  @Input() elementId?: number;
  @Input() comments: CommentDto[] = [];
  @Input() isRoot: boolean = true;

  constructor(private commentsService: CommentsService) {}

  ngOnInit(): void {
    if (this.isRoot && this.elementId) {
      this.commentsService.getComments(this.elementId).subscribe(data => {
        this.comments = data;
      });
    }
  }
}
