import {CommonModule} from '@angular/common';
import {Component, Input, OnInit} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {MatDividerModule} from '@angular/material/divider';
import {CommentDto} from '../../models/comment.dto';
import {CommentsService} from '../../services/comments.service';
import {CommentComponent} from '../comment/comment.component';
import {CommentFormComponent} from '../comment-form/comment-form.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';

@Component({
  selector: 'app-comments-list',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    MatButtonModule,
    MatDividerModule,
    CommentComponent,
    CommentFormComponent,
    ZxButtonComponent
  ],
  templateUrl: './comments-list.component.html',
  styleUrls: ['./comments-list.component.scss']
})
export class CommentsListComponent implements OnInit {
  @Input() elementId?: number;
  @Input() comments: CommentDto[] = [];
  @Input() isRoot: boolean = true;

  showForm = false;

  constructor(private commentsService: CommentsService) {}

  ngOnInit(): void {
    if (this.isRoot && this.elementId) {
      this.loadComments();
    }
  }

  loadComments(): void {
    if (this.elementId) {
      this.commentsService.getComments(this.elementId).subscribe(data => {
        this.comments = data;
      });
    }
  }

  onCommentSaved(comment: CommentDto): void {
    this.showForm = false;
    this.loadComments();
  }
}
